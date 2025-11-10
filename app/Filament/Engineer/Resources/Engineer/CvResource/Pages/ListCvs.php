<?php

namespace App\Filament\Engineer\Resources\Engineer\CvResource\Pages;

use App\Filament\Engineer\Resources\Engineer\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCvs extends ListRecords
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
          //  Actions\CreateAction::make(),
        ];
    }
}
