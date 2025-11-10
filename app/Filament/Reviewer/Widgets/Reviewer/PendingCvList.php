<?php

namespace App\Filament\Reviewer\Widgets;

use App\Models\Cv;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PendingCvList extends BaseWidget
{
    protected static ?string $heading = 'آخر السير الذاتية المعلقة';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cv::query()
                    ->where('cv_status', 'قيد الانتظار')
                    ->orderByDesc('created_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('صاحب السيرة'),
                TextColumn::make('experience')
                    ->label('الخبرة')
                    ->limit(50),
                TextColumn::make('education')
                    ->label('التعليم')
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('تاريخ التقديم')
                    ->dateTime(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Cv $record): string => \App\Filament\Reviewer\Resources\Reviewer\CvResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}