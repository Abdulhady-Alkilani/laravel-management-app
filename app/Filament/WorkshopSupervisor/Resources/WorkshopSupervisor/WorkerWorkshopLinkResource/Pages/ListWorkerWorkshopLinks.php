<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\WorkerWorkshopLinkResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\WorkerWorkshopLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWorkerWorkshopLinks extends ListRecords
{
    protected static string $resource = WorkerWorkshopLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
