<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource;
use App\Models\Report;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewReport extends ViewRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // زر التعديل يظهر فقط إذا كانت هناك صلاحية تعديل لهذا السجل
            Actions\EditAction::make(),//->visible(fn (Report $record) => Auth::user()->can('update', $record)),
            Actions\DeleteAction::make()->visible(fn (Report $record) => $record->employee_id === Auth::id()),
        ];
    }
}