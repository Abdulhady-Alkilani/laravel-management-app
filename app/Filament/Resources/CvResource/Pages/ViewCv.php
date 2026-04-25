<?php

namespace App\Filament\Resources\CvResource\Pages;

use App\Filament\Resources\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCv extends ViewRecord
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
