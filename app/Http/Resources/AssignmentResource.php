<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->sqid,
            'requirement_id' => $this->idToSqid('requirement_id'),
            'actor_id' => $this->idToSqid('actor_id'),
        ];
    }
}
