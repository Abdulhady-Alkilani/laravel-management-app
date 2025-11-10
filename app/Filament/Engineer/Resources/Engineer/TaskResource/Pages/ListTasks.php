<?php

namespace App\Filament\Engineer\Resources\Engineer\TaskResource\Pages;

use App\Filament\Engineer\Resources\Engineer\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  Actions\CreateAction::make(),
        ];
    }
}
