<?php

namespace Spectacular\Core\Actions\Requirements;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Requirement;

class CompleteRequirementTasks
{
    use AsAction;

    public function handle(Requirement $requirement): Collection
    {
        return $requirement->tasks
            ->each->complete()
            ->map->only('id', 'is_complete');
    }

    public function asController(Requirement $requirement): JsonResource
    {
        return new JsonResource($this->handle($requirement));
    }
}
