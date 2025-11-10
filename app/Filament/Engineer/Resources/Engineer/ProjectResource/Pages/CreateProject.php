<?php

namespace App\Filament\Engineer\Resources\Engineer\ProjectResource\Pages;

use App\Filament\Engineer\Resources\Engineer\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
}
