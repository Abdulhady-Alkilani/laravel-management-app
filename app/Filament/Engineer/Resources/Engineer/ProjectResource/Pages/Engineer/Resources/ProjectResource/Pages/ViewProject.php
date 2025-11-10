<?php

namespace App\Filament\Engineer\Resources\Engineer\ProjectResource\Pages;

use App\Filament\Engineer\Resources\Engineer\ProjectResource;
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