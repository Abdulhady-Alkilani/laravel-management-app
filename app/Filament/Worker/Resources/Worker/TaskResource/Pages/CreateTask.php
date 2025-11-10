<?php

namespace App\Filament\Worker\Resources\Worker\TaskResource\Pages;

use App\Filament\Worker\Resources\Worker\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
}
