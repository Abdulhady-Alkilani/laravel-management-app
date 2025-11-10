<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Task;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class OverdueTasksList extends BaseWidget
{
    protected static ?string $heading = 'المهام المتأخرة/الحرجة';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $managerId = auth()->id();

        return $table
            ->query(
                Task::query()
                    ->whereHas('project', fn (Builder $query) => $query->where('manager_user_id', $managerId))
                    ->where('end_date_planned', '<', Carbon::today())
                    ->whereNotIn('status', ['مكتملة', 'متوقفة'])
                    ->orderByDesc('end_date_planned')
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
                TextColumn::make('end_date_planned')
                    ->label('تاريخ الانتهاء المخطط')
                    ->date()
                    ->color('danger'),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'قيد التنفيذ' => 'primary',
                        'لم تبدأ' => 'info',
                        'متوقفة' => 'warning',
                        default => 'secondary',
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض التفاصيل')
                    // <== هنا التعديل: التأكد من أن المسار يشير إلى الـ Resource Class الصحيح (Manager/Resources/TaskResource)
                    ->url(fn (Task $record): string => \App\Filament\Manager\Resources\Manager\TaskResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}