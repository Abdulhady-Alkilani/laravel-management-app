<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewServiceProposals extends ListRecords
{
    protected static string $resource = NewServiceProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
