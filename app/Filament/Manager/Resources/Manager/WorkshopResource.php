<?php

namespace App\Filament\Manager\Resources\Manager;

use App\Filament\Manager\Resources\Manager\WorkshopResource\Pages;
use App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers;
use App\Models\Project; // استيراد موديل Project
use App\Models\Workshop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Tables\Actions\Action; // استيراد Action

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'الورشات';
    protected static ?string $pluralModelLabel = 'الورشات';
    protected static ?string $modelLabel = 'ورشة';

    public static function getEloquentQuery(): Builder
    {
        // عرض الورشات المرتبطة بمشاريع يديرها المدير الحالي فقط
        return parent::getEloquentQuery()->whereHas('project', function (Builder $query) {
            $query->where('manager_user_id', Auth::id());
        });
    }

    protected static bool $canCreate = false; // مدير المشروع لا ينشئ ورش
    protected static bool $canEdit = true;
    protected static bool $canDelete = false; // مدير المشروع لا يحذف ورش

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('اسم الورشة')
                    ->disabled(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull()
                    ->label('وصف الورشة')
                    ->disabled(),
                // حقل المشروع هنا يعرض كقراءة فقط لأنه تم تحديد المشروع في getEloquentQuery
                Forms\Components\Placeholder::make('project_info')
                    ->content(fn (Workshop $record) => $record?->project->name ?? 'غير مرتبطة بمشروع')
                    ->label('المشروع التابع له الورشة'),
                Forms\Components\Placeholder::make('supervisor_info')
                    ->content(fn (Workshop $record) => $record?->supervisor->name ?? 'لا يوجد مشرف')
                    ->label('مشرف الورشة'),
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
                // Custom Action لربط ورشة بمشروع من مشاريع المدير الحالي
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
                                $managerId = Auth::id();
                                return Workshop::query()
                                    ->where(fn (Builder $query) => $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('description', 'like', "%{$search}%"))
                                    ->where(function (Builder $query) use ($managerId) {
                                        // ورشات غير مرتبطة بأي مشروع
                                        $query->whereNull('project_id')
                                              // أو ورشات مرتبطة بمشروع لا يديره المدير الحالي
                                              ->orWhereDoesntHave('project', fn (Builder $subQuery) => $subQuery->where('manager_user_id', $managerId));
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
                            // تصفية المشاريع لتظهر فقط مشاريع المدير الحالي
                            ->relationship('project', 'name', fn (Builder $query) => $query->where('manager_user_id', Auth::id()))
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
                // إجراء فك الارتباط هنا يمكن أن يفكها من مشروع المدير
                Action::make('detach_from_project_resource')
                    ->label('فك الارتباط')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Workshop $record) {
                        // التأكد أن الورشة تتبع مشروع يديره هذا المدير قبل فك الارتباط
                        if ($record->project && $record->project->manager_user_id === Auth::id()) {
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
            RelationManagers\WorkersRelationManager::class,
            RelationManagers\TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
            'view' => Pages\ViewWorkshop::route('/{record}'),
        ];
    }
}