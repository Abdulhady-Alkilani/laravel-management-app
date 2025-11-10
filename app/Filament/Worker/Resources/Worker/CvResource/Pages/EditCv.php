<?php

namespace App\Filament\Worker\Resources\Worker\CvResource\Pages;

use App\Filament\Worker\Resources\Worker\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCv extends EditRecord
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
        //    Actions\DeleteAction::make(),
        ];
    }
}
