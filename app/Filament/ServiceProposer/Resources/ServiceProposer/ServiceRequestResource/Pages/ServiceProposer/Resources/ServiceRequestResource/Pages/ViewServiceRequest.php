<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewServiceRequest extends ViewRecord
{
    protected static string $resource = ServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا يوجد تعديل للمستخدم العادي
        ];
    }
}