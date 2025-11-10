<?php

namespace App\Filament\Worker\Resources\Worker\TaskResource\Pages;

use App\Filament\Worker\Resources\Worker\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
         //   Actions\CreateAction::make(),
        ];
    }
}
