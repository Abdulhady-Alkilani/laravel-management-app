<?php

namespace App\Filament\Worker\Resources\Worker\CvResource\Pages;

use App\Filament\Worker\Resources\Worker\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCvs extends ListRecords
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
