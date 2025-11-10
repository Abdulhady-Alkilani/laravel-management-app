<?php

namespace App\Filament\Investor\Resources\Investor\ReportResource\Pages;

use App\Filament\Investor\Resources\Investor\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا توجد أزرار تعديل أو حذف هنا
        ];
    }
}