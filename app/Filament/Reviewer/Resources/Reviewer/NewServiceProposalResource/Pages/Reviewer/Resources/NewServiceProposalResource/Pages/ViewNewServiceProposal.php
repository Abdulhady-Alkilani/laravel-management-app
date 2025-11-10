<?php

namespace App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource\Pages;

use App\Filament\Reviewer\Resources\Reviewer\NewServiceProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewNewServiceProposal extends ViewRecord
{
    protected static string $resource = NewServiceProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // تسمح للمراجع بتعديل الحالة مباشرة من صفحة العرض
            Actions\EditAction::make(),
        ];
    }
}