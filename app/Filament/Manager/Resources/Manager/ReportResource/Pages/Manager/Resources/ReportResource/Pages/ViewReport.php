<?php

namespace App\Filament\Manager\Resources\Manager\ReportResource\Pages;

use App\Filament\Manager\Resources\Manager\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // <== السماح بالانتقال من صفحة العرض إلى صفحة التعديل
        ];
    }
    // ViewRecord يستخدم form() من الـ Resource بشكل افتراضي، وهذا مناسب للعرض.
}