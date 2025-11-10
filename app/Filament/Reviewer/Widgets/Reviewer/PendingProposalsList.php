<?php

namespace App\Filament\Reviewer\Widgets;

use App\Models\NewServiceProposal;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PendingProposalsList extends BaseWidget
{
    protected static ?string $heading = 'آخر مقترحات الخدمات المعلقة';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                NewServiceProposal::query()
                    ->where('status', 'قيد المراجعة')
                    ->orderByDesc('proposal_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('proposed_service_name')
                    ->label('اسم المقترح'),
                TextColumn::make('proposer.name')
                    ->label('المقترح من'),
                TextColumn::make('proposal_date')
                    ->label('تاريخ الاقتراح')
                    ->date(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime(),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (NewServiceProposal $record): string => \App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}