<?php

namespace App\Filament\Resources\ProjectInvestorLinkResource\Pages;

use App\Filament\Resources\ProjectInvestorLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectInvestorLink extends EditRecord
{
    protected static string $resource = ProjectInvestorLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
