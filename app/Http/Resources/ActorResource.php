<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->created_at,
            'id' => $this->sqid,
            'name' => $this->name,
            'project_id' => $this->project_sqid,
            'summary' => $this->summary,
            'updated_at' => $this->updated_at,
            'weight' => $this->weight,
        ];
    }
}
