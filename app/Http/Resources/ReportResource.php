<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_type' => $this->report_type,
            'report_details' => $this->report_details,
            'report_status' => $this->report_status,
            'project' => $this->whenLoaded('project', function () {
                return $this->project ? [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ] : null;
            }),
            'workshop' => $this->whenLoaded('workshop', function () {
                return $this->workshop ? [
                    'id' => $this->workshop->id,
                    'name' => $this->workshop->name,
                ] : null;
            }),
            'service' => $this->whenLoaded('service', function () {
                return $this->service ? [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                ] : null;
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
