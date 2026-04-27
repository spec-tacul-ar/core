<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->sqid,
            'name' => $this->name,
            'estimate' => $this->estimate,
            'is_complete' => $this->is_complete,
            'requirement_id' => $this->idToSqid('requirement_id'),
            'weight' => $this->weight,
        ];
    }
}
