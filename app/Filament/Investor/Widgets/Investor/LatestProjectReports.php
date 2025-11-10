<?php

namespace App\Filament\Investor\Widgets;

use App\Models\Report;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestProjectReports extends BaseWidget
{
    protected static ?string $heading = 'أحدث تقارير المشاريع';
    protected static ?int $sort = 3;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $investorId = Auth::id();

        return $table
            ->query(
                Report::query()
                    ->whereHas('project.investors', fn (Builder $query) => $query->where('investor_user_id', $investorId)) // <== هنا التعديل: استخدام 'investor_user_id'
                    ->orderByDesc('created_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('report_type')
                    ->label('نوع التقرير'),
                TextColumn::make('project.name')
                    ->label('المشروع'),
                TextColumn::make('employee.name')
                    ->label('مقدم التقرير'),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
                TextColumn::make('report_status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Report $record): string => \App\Filament\Investor\Resources\Investor\ReportResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}