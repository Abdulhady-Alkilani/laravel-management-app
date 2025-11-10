<?php

namespace App\Filament\ServiceProposer\Widgets;

use App\Models\ServiceRequest;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestRequestsList extends BaseWidget
{
    protected static ?string $heading = 'آخر طلبات الخدمات';
    protected static ?int $sort = 2;
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        $userId = Auth::id();

        return $table
            ->query(
                ServiceRequest::query()
                    ->where('user_id', $userId)
                    ->orderByDesc('request_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('service.name')
                    ->label('الخدمة'),
                TextColumn::make('details')
                    ->label('التفاصيل')
                    ->limit(50),
                TextColumn::make('request_date')
                    ->label('تاريخ الطلب')
                    ->date(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (ServiceRequest $record): string => \App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}