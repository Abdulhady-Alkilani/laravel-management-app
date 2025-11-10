<?php

namespace App\Filament\Engineer\Resources\Engineer\TaskResource\Pages;

use App\Filament\Engineer\Resources\Engineer\TaskResource;
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