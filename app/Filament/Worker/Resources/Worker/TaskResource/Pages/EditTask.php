<?php

namespace App\Filament\Worker\Resources\Worker\TaskResource\Pages;

use App\Filament\Worker\Resources\Worker\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
       //     Actions\DeleteAction::make(),
        ];
    }
}
