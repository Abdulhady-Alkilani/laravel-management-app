<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceRequest extends EditRecord
{
    protected static string $resource = ServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
