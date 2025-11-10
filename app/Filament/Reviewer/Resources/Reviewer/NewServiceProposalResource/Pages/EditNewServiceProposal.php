<?php

namespace App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource\Pages;

use App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNewServiceProposal extends EditRecord
{
    protected static string $resource = NewServiceProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
        //    Actions\DeleteAction::make(),
        ];
    }
}
