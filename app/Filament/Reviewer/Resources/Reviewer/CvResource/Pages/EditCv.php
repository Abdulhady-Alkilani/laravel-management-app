<?php

namespace App\Filament\Reviewer\Resources\Reviewer\CvResource\Pages;

use App\Filament\Reviewer\Resources\Reviewer\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCv extends EditRecord
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // Actions\DeleteAction::make(),
        ];
    }
}
