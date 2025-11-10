<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
