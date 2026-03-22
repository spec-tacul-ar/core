<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'description' => $this->description,
            'id' => $this->id,
            'name' => $this->name,
            'project_id' => (int) $this->project_id,
            'requirements' => RequirementResource::collection($this->whenLoaded('requirements')),
            'updated_at' => $this->updated_at,
            'weight' => $this->weight,
        ];
    }
}
