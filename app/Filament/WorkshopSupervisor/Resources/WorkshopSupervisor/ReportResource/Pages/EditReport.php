<?php

namespace App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource\Pages;

use App\Filament\WorkshopSupervisor\Resources\WorkshopSupervisor\ReportResource;
use App\Models\Report;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditReport extends EditRecord
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(fn (Report $record) => $record->employee_id === Auth::id()),
            Actions\ViewAction::make(),
        ];
    }
}