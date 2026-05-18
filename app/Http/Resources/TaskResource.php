<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $project = $this->requirement->feature->project;

        return [
            'id' => $this->sqid,
            'name' => $this->name,
            'estimate' => $request->user()?->can('viewEstimates', $project) ? $this->estimate : null,
            'is_complete' => $this->is_complete,
            'requirement_id' => $this->requirement_sqid,
            'weight' => $this->weight,
        ];
    }
}
