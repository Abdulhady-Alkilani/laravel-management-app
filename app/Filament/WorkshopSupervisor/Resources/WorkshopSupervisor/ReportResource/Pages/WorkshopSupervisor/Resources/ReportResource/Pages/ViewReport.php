<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}