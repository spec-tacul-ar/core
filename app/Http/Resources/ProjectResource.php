<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'features' => FeatureResource::collection($this->whenLoaded('features')),
            'description' => $this->description,
            'name' => $this->name,
            'requirements_count' => $this->requirements_count,
            'blocked_requirements_count' => $this->blocked_requirements_count,
            'unknowns_count' => $this->unknowns_count,
            'tasks_count' => $this->tasks_count,
            'requirements_with_tasks_count' => $this->requirements_with_tasks_count,
            'requirements_all_tasks_complete_count' => $this->requirements_all_tasks_complete_count,
            'contributors' => ContributorResource::collection($this->whenLoaded('contributors')),
            'invitations' => InvitationResource::collection($this->whenLoaded('invitations')),
            'readmark' => $this->readmark?->updated_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
