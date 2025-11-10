<?php

namespace App\Filament\Investor\Resources\Investor\ProjectResource\Pages;

use App\Filament\Investor\Resources\Investor\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا توجد أزرار تعديل أو حذف هنا
        ];
    }
}