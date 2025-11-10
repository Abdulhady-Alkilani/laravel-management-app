<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNewServiceProposal extends CreateRecord
{
    protected static string $resource = NewServiceProposalResource::class;
}
