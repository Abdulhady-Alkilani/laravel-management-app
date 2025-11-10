<?php

namespace App\Filament\Engineer\Resources\Engineer\TaskResource\Pages;

use App\Filament\Engineer\Resources\Engineer\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;
}
