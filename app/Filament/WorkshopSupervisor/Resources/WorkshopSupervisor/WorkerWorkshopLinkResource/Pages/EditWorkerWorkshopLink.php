<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\WorkerWorkshopLinkResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\WorkerWorkshopLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkerWorkshopLink extends EditRecord
{
    protected static string $resource = WorkerWorkshopLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
