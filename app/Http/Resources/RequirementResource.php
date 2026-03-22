<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequirementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'assignments' => AssignmentResource::collection($this->whenLoaded('assignments')),
            'blocked_reason' => $this->blocked_reason,
            'description' => $this->description,
            'id' => $this->id,
            'name' => $this->name,
            'feature_id' => (int) $this->feature_id,
            'unknowns' => UnknownResource::collection($this->whenLoaded('unknowns')),
            'reference' => $this->reference,
            'source' => $this->source,
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'weight' => $this->weight,
            'updated_at' => $this->updated_at,
        ];
    }
}
