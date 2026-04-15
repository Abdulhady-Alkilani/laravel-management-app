<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'budget' => $this->budget,
            'start_date' => $this->start_date?->toDateString(),
            'end_date_planned' => $this->end_date_planned?->toDateString(),
            'end_date_actual' => $this->end_date_actual?->toDateString(),
            'status' => $this->status,
            'manager' => $this->whenLoaded('manager', function () {
                return $this->manager ? [
                    'id' => $this->manager->id,
                    'name' => $this->manager->name,
                ] : null;
            }),
            'workshops' => $this->whenLoaded('workshops', function () {
                return $this->workshops->map(fn ($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                ]);
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
