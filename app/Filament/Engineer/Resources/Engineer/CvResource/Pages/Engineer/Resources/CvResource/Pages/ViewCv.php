<?php

namespace App\Filament\Engineer\Resources\Engineer\CvResource\Pages;

use App\Filament\Engineer\Resources\Engineer\CvResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCv extends ViewRecord
{
    protected static string $resource = CvResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // <== السماح بالانتقال من صفحة العرض إلى صفحة التعديل
        ];
    }
    // ViewRecord يستخدم form() من الـ Resource بشكل افتراضي، وهذا مناسب للعرض.
}