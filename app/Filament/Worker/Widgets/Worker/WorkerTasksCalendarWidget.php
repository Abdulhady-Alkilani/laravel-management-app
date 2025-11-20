<?php

namespace App\Filament\Worker\Widgets;

use App\Models\Task;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkerTasksCalendarWidget extends BaseWidget
{
    protected static ?string $heading = 'مهامي المجدولة';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $workerId = Auth::id();

        return $table
            ->query(
                Task::query()
                    ->where('assigned_to_user_id', $workerId)
                    ->whereNotIn('status', ['مكتملة', 'متوقفة']) // المهام النشطة
                    ->where('end_date_planned', '>=', Carbon::today()) // المهام المستقبلية أو الحالية
                    ->orderBy('end_date_planned')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('description')
                    ->label('الوصف')
                    ->html()
                    ->limit(50),
                TextColumn::make('project.name')
                    ->label('المشروع'),
                TextColumn::make('workshop.name')
                    ->label('الورشة'),
                TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء')
                    ->date(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Task $record): string => \App\Filament\Worker\Resources\Worker\TaskResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}