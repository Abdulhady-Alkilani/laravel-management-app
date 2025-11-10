<?php

namespace App\Filament\Reviewer\Resources\Reviewer\CvResource\Pages;

use App\Filament\Reviewer\Resources\Reviewer\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCv extends ViewRecord
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // <== لإضافة زر التعديل في صفحة العرض
        ];
    }
}