<?php

namespace App\Filament\Investor\Resources\Investor\ReportResource\Pages;

use App\Filament\Investor\Resources\Investor\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  Actions\DeleteAction::make(),
        ];
    }
}
