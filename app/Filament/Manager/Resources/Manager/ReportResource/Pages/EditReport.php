<?php

namespace App\Filament\Manager\Resources\Manager\ReportResource\Pages;

use App\Filament\Manager\Resources\Manager\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
