<?php

namespace App\Filament\WorkshopSupervisor\Widgets;

use App\Models\Task;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WorkshopTasksOverview extends BaseWidget
{
    protected static ?string $heading = 'مهام الورشة الحالية';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $supervisorId = Auth::id();

        return $table
            ->query(
                Task::query()
                    ->whereHas('workshop', fn (Builder $query) => $query->where('supervisor_user_id', $supervisorId))
                    ->whereNotIn('status', ['مكتملة', 'متوقفة']) // عرض المهام غير المكتملة
                    ->orderBy('end_date_planned')
            )
            ->columns([
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),
                TextColumn::make('project.name')
                    ->label('المشروع'),
                TextColumn::make('workshop.name')
                    ->label('الورشة'),
                TextColumn::make('assignedTo.name')
                    ->label('المسؤول'),
                TextColumn::make('progress')
                    ->label('التقدم (%)'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
                TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء')
                    ->date(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('تعديل')
                    ->url(fn (Task $record): string => \App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil'),
            ]);
    }
}