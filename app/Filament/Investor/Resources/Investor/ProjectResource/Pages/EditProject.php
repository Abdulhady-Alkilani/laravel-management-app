<?php

namespace App\Filament\Investor\Resources\Investor\ProjectResource\Pages;

use App\Filament\Investor\Resources\Investor\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\DeleteAction::make(),
        ];
    }
}
