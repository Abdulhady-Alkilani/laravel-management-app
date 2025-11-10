<?php

namespace App\Filament\Engineer\Widgets;

use App\Models\Report;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyRecentReports extends BaseWidget
{
    protected static ?string $heading = 'آخر تقاريري المرسلة';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $engineerId = Auth::id();

        return $table
            ->query(
                Report::query()
                    ->where('employee_id', $engineerId)
                    ->orderByDesc('created_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('report_type')
                    ->label('نوع التقرير'),
                TextColumn::make('project.name')
                    ->label('المشروع'),
                TextColumn::make('workshop.name')
                    ->label('الورشة'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextColumn::make('report_status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Report $record): string => \App\Filament\Engineer\Resources\Engineer\ReportResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}