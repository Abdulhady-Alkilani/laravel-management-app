<?php

namespace App\Filament\Resources\WorkerWorkshopLinkResource\Pages;

use App\Filament\Resources\WorkerWorkshopLinkResource;
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
