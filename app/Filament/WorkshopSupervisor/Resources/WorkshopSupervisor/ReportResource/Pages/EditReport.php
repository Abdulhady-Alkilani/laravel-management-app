<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }
}