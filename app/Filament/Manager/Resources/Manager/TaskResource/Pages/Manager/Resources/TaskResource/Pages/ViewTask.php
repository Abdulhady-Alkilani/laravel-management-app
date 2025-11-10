<?php

namespace App\Filament\Manager\Resources\Manager\TaskResource\Pages;

use App\Filament\Manager\Resources\Manager\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // <== السماح بالانتقال من صفحة العرض إلى صفحة التعديل
        ];
    }
    // ViewRecord يستخدم form() من الـ Resource بشكل افتراضي، وهذا مناسب للعرض.
}