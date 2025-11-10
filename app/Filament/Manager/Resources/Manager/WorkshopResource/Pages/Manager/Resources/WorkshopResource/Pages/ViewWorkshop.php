<?php

namespace App\Filament\Manager\Resources\Manager\WorkshopResource\Pages;

use App\Filament\Manager\Resources\Manager\WorkshopResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWorkshop extends ViewRecord
{
    protected static string $resource = WorkshopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // <== السماح بالانتقال من صفحة العرض إلى صفحة التعديل
        ];
    }
    // ViewRecord يستخدم form() من الـ Resource بشكل افتراضي، وهذا مناسب للعرض.
}