<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
