<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'id' => $this->id,
            'name' => $this->name,
            'project_id' => (int) $this->project_id,
            'summary' => $this->summary,
            'updated_at' => $this->updated_at,
            'weight' => $this->weight,
        ];
    }
}
