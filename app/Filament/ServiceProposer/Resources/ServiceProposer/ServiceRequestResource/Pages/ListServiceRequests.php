<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceRequests extends ListRecords
{
    protected static string $resource = ServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
