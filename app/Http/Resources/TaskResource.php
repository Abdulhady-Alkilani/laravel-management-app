<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'progress' => $this->progress,
            'status' => $this->status,
            'start_date' => $this->start_date?->toDateString(),
            'end_date_planned' => $this->end_date_planned?->toDateString(),
            'actual_end_date' => $this->actual_end_date?->toDateString(),
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ];
            }),
            'workshop' => $this->whenLoaded('workshop', function () {
                return $this->workshop ? [
                    'id' => $this->workshop->id,
                    'name' => $this->workshop->name,
                ] : null;
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
