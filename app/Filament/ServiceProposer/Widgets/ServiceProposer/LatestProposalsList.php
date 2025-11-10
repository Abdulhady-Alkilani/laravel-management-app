<?php

namespace App\Filament\ServiceProposer\Widgets;

use App\Models\NewServiceProposal;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LatestProposalsList extends BaseWidget
{
    protected static ?string $heading = 'آخر اقتراحات الخدمات';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        $userId = Auth::id();

        return $table
            ->query(
                NewServiceProposal::query()
                    ->where('user_id', $userId)
                    ->orderByDesc('proposal_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('proposed_service_name')
                    ->label('اسم المقترح'),
                TextColumn::make('details')
                    ->label('التفاصيل')
                    ->limit(50),
                TextColumn::make('proposal_date')
                    ->label('تاريخ الاقتراح')
                    ->date(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (NewServiceProposal $record): string => \App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}