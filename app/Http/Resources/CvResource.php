<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CvResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'profile_details' => $this->profile_details,
            'experience' => $this->experience,
            'education' => $this->education,
            'cv_file_url' => $this->cv_file_path ? asset('storage/' . $this->cv_file_path) : null,
            'ai_score' => $this->ai_score,
            'cv_status' => $this->cv_status,
            'rejection_reason' => $this->rejection_reason,
            'skills' => $this->whenLoaded('skills', function () {
                return $this->skills->map(fn ($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                ]);
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
