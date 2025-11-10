<?php

namespace App\Filament\Engineer\Resources\Engineer\ReportResource\Pages;

use App\Filament\Engineer\Resources\Engineer\ReportResource;
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
