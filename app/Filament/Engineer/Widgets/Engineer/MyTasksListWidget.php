<?php

namespace App\Filament\Engineer\Widgets;

use App\Models\Task;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MyTasksListWidget extends BaseWidget
{
    protected static ?string $heading = 'مهامي المعلقة والمكتملة';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $engineerId = Auth::id();

        return $table
            ->query(
                Task::query()
                    ->where('assigned_to_user_id', $engineerId)
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
                EditAction::make()
                    ->url(fn (Task $record): string => \App\Filament\Engineer\Resources\Engineer\TaskResource::getUrl('edit', ['record' => $record]))
                    ->icon('heroicon-o-pencil'),
            ]);
    }
}