<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\WorkshopResource\Pages;
use App\Models\Workshop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use Illuminate\Validation\Rules\Unique; // <== التعديل هنا: استخدام الكلاس الصحيح من Laravel
use Filament\Tables\Actions\Action;

// استيراد Relation Managers الخاصة بالعمال والمهام
use App\Filament\WorkshopSupervisor\Resources\WorkshopResource\RelationManagers\TasksRelationManager;
use App\Filament\WorkshopSupervisor\Resources\WorkshopResource\RelationManagers\WorkersRelationManager;

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'ورشاتي';
    protected static ?string $pluralModelLabel = 'ورشاتي';
    protected static ?string $modelLabel = 'ورشة';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('supervisor_user_id', Auth::id());
    }

    protected static bool $canCreate = true;
    protected static bool $canEdit = true;
    protected static bool $canDelete = true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        ignoreRecord: true,
                        // <== التعديل هنا: تغيير نوع التلميح إلى Illuminate\Validation\Rules\Unique
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('supervisor_user_id', Auth::id())
                    )
                    ->label('اسم الورشة')
                    ->helperText('يجب أن يكون اسم الورشة فريداً ضمن ورشاتك.'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->nullable()
                    ->label('وصف الورشة'),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'name', fn (Builder $query) => 
                        $query->whereHas('workshops', fn (Builder $subQuery) => $subQuery->where('supervisor_user_id', Auth::id()))
                              ->orWhereDoesntHave('workshops') // يمكن ربطها بمشاريع ليس بها ورش بعد
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('المشروع المرتبط')
                    ->helperText('يمكنك ربط هذه الورشة بمشروع تابعه.'),
                
                Forms\Components\Hidden::make('supervisor_user_id')
                    ->default(Auth::id())
                    ->dehydrated(true),
                Forms\Components\Placeholder::make('supervisor_info')
                    ->content(fn () => Auth::user()->name)
                    ->label('المشرف الحالي'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الورشة'),
                Tables\Columns\TextColumn::make('project.name')
                    ->searchable()
                    ->sortable()
                    ->label('المشروع'),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->searchable()
                    ->sortable()
                    ->label('مشرف الورشة'),
                Tables\Columns\TextColumn::make('workers_count')
                    ->counts('workers')
                    ->label('عدد العمال'),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('عدد المهام'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('تاريخ الإنشاء'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('assignWorkshopToMyProject')
                    ->label('ربط ورشة بمشروع من مشاريعي')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Forms\Components\Select::make('selected_workshop_id')
                            ->label('اختر ورشة للربط')
                            ->helperText('اختر ورشة غير مرتبطة بمشاريعك حالياً، أو غير مرتبطة بأي مشروع.')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $supervisorId = Auth::id();
                                return Workshop::query()
                                    ->where(fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('description', 'like', "%{$search}%"))
                                    ->where(function (Builder $query) use ($supervisorId) {
                                        $query->whereNull('project_id')
                                              ->orWhereDoesntHave('project', fn (Builder $subQuery) => $subQuery->where('manager_user_id', $supervisorId));
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (Workshop $workshop) => [
                                        $workshop->id => "{$workshop->name}" . (filled($workshop->description) ? " ({$workshop->description})" : '')
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelFromRecordUsing(fn (Workshop $record) => "{$record->name}" . (filled($record->description) ? " ({$record->description})" : '')),

                        Forms\Components\Select::make('target_project_id')
                            ->label('اختر المشروع المراد الربط به')
                            ->helperText('اختر أحد مشاريعك لربط الورشة به.')
                            ->required()
                            ->relationship('project', 'name', fn (Builder $query) => $query->whereHas('workshops', fn (Builder $subQuery) => $subQuery->where('supervisor_user_id', Auth::id())))
                            ->searchable()
                            ->preload(),
                    ])
                    ->action(function (array $data) {
                        $workshop = Workshop::find($data['selected_workshop_id']);
                        $project = Project::find($data['target_project_id']);

                        if ($workshop && $project) {
                            $workshop->project_id = $project->id;
                            $workshop->save();
                            \Filament\Notifications\Notification::make()
                                ->title('تم الربط بنجاح')
                                ->body('تم ربط الورشة بالمشروع المحدد.')
                                ->success()
                                ->send();
                        } else {
                             \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('حدث خطأ أثناء محاولة ربط الورشة.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('detach_from_project_resource')
                    ->label('فك الارتباط')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Workshop $record) {
                        if ($record->project && $record->project->workshops()->where('supervisor_user_id', Auth::id())->exists()) {
                            $record->project_id = null;
                            $record->save();
                            \Filament\Notifications\Notification::make()
                                ->title('تم فك الارتباط')
                                ->body('تم فك ارتباط الورشة بالمشروع بنجاح.')
                                ->success()
                                ->send();
                        } else {
                             \Filament\Notifications\Notification::make()
                                ->title('خطأ')
                                ->body('ليس لديك صلاحية فك ارتباط هذه الورشة من مشروع آخر.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                // يمكن إضافة إجراء فك ارتباط جماعي مخصص هنا إذا لزم الأمر
            ]);
    }

    public static function getRelations(): array
    {
        return [
            WorkersRelationManager::class,
            TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'view' => Pages\ViewWorkshop::route('/{record}'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
        ];
    }
}