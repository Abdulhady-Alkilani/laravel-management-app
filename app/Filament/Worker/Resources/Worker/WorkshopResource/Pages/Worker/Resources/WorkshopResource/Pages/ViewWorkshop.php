<?php

namespace App\Filament\Worker\Resources\Worker\WorkshopResource\Pages;

use App\Filament\Worker\Resources\Worker\WorkshopResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkshop extends ViewRecord
{
    protected static string $resource = WorkshopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا توجد أزرار تعديل أو حذف هنا
        ];
    }
}