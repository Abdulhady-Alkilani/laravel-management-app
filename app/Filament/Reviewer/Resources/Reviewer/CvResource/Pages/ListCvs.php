<?php

namespace App\Filament\Reviewer\Resources\Reviewer\CvResource\Pages;

use App\Filament\Reviewer\Resources\Reviewer\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCvs extends ListRecords
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\CreateAction::make(),
        ];
    }
}
