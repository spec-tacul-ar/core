<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project_name' => $this->whenLoaded('project', fn () => $this->project->name),
            'account_name' => $this->whenLoaded('account', fn () => $this->account->name),
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
