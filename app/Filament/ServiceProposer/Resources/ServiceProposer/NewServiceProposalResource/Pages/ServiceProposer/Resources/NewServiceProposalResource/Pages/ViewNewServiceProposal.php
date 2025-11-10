<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\NewServiceProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewServiceProposal extends ViewRecord
{
    protected static string $resource = NewServiceProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // لا يوجد تعديل للمستخدم العادي
        ];
    }
}