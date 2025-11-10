<?php

namespace App\Filament\Worker\Widgets;

use App\Models\Task;
use Filament\Tables\Actions\EditAction; // للعامل يمكنه التعديل
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MyUrgentTasks extends BaseWidget
{
    protected static ?string $heading = 'مهامي العاجلة/المتأخرة';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $workerId = Auth::id();

        return $table
            ->query(
                Task::query()
                    ->where('assigned_to_user_id', $workerId)
                    ->whereNotIn('status', ['مكتملة', 'متوقفة'])
                    ->where(fn ($query) => $query->where('end_date_planned', '<=', Carbon::today()->addDays(7)) // تنتهي خلال 7 أيام
                                                ->orWhere('end_date_planned', '<', Carbon::today())) // أو متأخرة بالفعل
                    ->orderBy('end_date_planned')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(50),
                TextColumn::make('project.name')
                    ->label('المشروع'),
                TextColumn::make('workshop.name')
                    ->label('الورشة'),
                TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء')
                    ->date()
                    ->color(fn (Carbon $date) => $date->isPast() ? 'danger' : 'warning'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                EditAction::make() // للعامل يمكنه تعديل المهمة مباشرة
                    ->url(fn (Task $record): string => \App\Filament\Worker\Resources\Worker\TaskResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil'),
            ]);
    }
}