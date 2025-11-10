<?php

namespace App\Filament\Manager\Resources\Manager\WorkshopResource\Pages;

use App\Filament\Manager\Resources\Manager\WorkshopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkshops extends ListRecords
{
    protected static string $resource = WorkshopResource::class;

    protected function getHeaderActions(): array
    {
        return [
         //   Actions\CreateAction::make(),
        ];
    }
}
