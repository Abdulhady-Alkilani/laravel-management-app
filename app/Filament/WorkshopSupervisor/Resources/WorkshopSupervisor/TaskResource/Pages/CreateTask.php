<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
}
