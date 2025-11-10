<?php

namespace App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource\Pages;

use App\Filament\ServiceProposer\Resources\ServiceProposer\ServiceRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceRequest extends CreateRecord
{
    protected static string $resource = ServiceRequestResource::class;
}
