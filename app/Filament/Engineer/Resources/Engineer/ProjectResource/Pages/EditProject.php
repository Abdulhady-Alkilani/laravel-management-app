<?php

namespace App\Filament\Engineer\Resources\Engineer\ProjectResource\Pages;

use App\Filament\Engineer\Resources\Engineer\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\DeleteAction::make(),
        ];
    }
}
