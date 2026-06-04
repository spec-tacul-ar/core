<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CollaborationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->sqid,
            'account_id' => $this->account_sqid,
            'project_id' => $this->project_sqid,
            'account_name' => $this->whenLoaded('account', fn() => $this->account->name),
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
