<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->sqid,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'read_at' => $this->whenLoaded('collaborations', fn() => $this->collaborations->first()?->read_at),
            'archived_at' => $this->archived_at,

            'actors' => ActorResource::collection($this->whenLoaded('actors')),
            'features' => FeatureResource::collection($this->whenLoaded('features')),
            'collaborations' => CollaborationResource::collection($this->whenLoaded('collaborations')),
            'invitations' => InvitationResource::collection($this->whenLoaded('invitations')),

            'requirements_count' => $this->requirements_count,
            'blocked_requirements_count' => $this->blocked_requirements_count,
            'unknowns_count' => $this->unknowns_count,
            'tasks_count' => $this->tasks_count,
            'collaborations_count' => $this->collaborations_count,
            'requirements_with_tasks_count' => $this->requirements_with_tasks_count,
            'requirements_all_tasks_complete_count' => $this->requirements_all_tasks_complete_count,
        ];
    }
}
