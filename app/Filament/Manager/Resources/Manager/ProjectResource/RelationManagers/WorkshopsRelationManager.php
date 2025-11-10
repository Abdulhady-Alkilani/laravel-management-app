<?php

namespace App\Filament\Manager\Resources\Manager\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action; // استيراد Action بدلاً من AttachAction

class WorkshopsRelationManager extends RelationManager
{
    protected static string $relationship = 'workshops';
    protected static ?string $title = 'الورشات التابعة';
    protected static ?string $pluralTitle = 'الورشات التابعة';
    protected static ?string $modelLabel = 'ورشة';
    protected static ?string $pluralModelLabel = 'ورشات';

    protected static bool $canCreate = false;

    public function form(Form $form): Form
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
                Forms\Components\Placeholder::make('supervisor_info')
                    ->content(fn (Workshop $record) => $record?->supervisor->name ?? 'لا يوجد مشرف')
                    ->label('مشرف الورشة'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('اسم الورشة'),
                Tables\Columns\TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('supervisor.name')
                    ->label('مشرف الورشة'),
                Tables\Columns\TextColumn::make('workers_count')
                    ->counts('workers')
                    ->label('عدد العمال'),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('عدد المهام'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('attachWorkshop')
                    ->label('إرفاق ورشة موجودة')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Forms\Components\Select::make('workshop_id')
                            ->label('اختر ورشة')
                            ->helperText('اختر ورشة غير مرتبطة حالياً بأي مشروع لربطها بهذا المشروع.')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search): array {
                                // <== التعديل الرئيسي هنا: فقط الورش التي لا ترتبط بأي مشروع
                                return Workshop::query()
                                    ->whereNull('project_id') // فقط الورش التي ليس لها project_id
                                    ->where(function (Builder $query) use ($search) {
                                        $query->where('name', 'like', "%{$search}%")
                                              ->orWhere('description', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (Workshop $workshop) => [
                                        $workshop->id => "{$workshop->name}" . (filled($workshop->description) ? " ({$workshop->description})" : '')
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelFromRecordUsing(fn (Workshop $record) => "{$record->name}" . (filled($record->description) ? " ({$record->description})" : '')),
                    ])
                    ->action(function (array $data) {
                        $workshop = Workshop::find($data['workshop_id']);
                        if ($workshop) {
                            $workshop->project_id = $this->getOwnerRecord()->id; // ربط الورشة بالمشروع الحالي
                            $workshop->save();
                            \Filament\Notifications\Notification::make()
                                ->title('تم الإرفاق بنجاح')
                                ->body('تم ربط الورشة بالمشروع.')
                                ->success()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('detach_from_project')
                    ->label('فك الارتباط')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Workshop $record) {
                        $record->project_id = null;
                        $record->save();
                        \Filament\Notifications\Notification::make()
                            ->title('تم فك الارتباط')
                            ->body('تم فك ارتباط الورشة بالمشروع بنجاح.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Action::make('detachSelected')
                        ->label('فك الارتباط المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Tables\Actions\BulkAction $action) {
                            foreach ($action->getSelectedRecords() as $record) {
                                $record->project_id = null;
                                $record->save();
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('تم فك الارتباط')
                                ->body('تم فك ارتباط الورشات المختارة بالمشروع بنجاح.')
                                ->success()
                                ->send();
                        })
                ]),
            ]);
    }
}