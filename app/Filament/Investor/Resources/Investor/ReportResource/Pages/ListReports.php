<?php

namespace App\Filament\Investor\Resources\Investor\ReportResource\Pages;

use App\Filament\Investor\Resources\Investor\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
         //   Actions\CreateAction::make(),
        ];
    }
}
