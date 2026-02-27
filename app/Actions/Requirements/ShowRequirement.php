<?php

namespace Spectacular\Core\Actions\Requirements;

use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\RequirementResource;
use Spectacular\Core\Models\Requirement;

class ShowRequirement
{
    use AsAction;

    public function handle(Requirement $requirement): Requirement
    {
        return $requirement;
    }

    public function asController(Requirement $requirement): RequirementResource
    {
        return new RequirementResource($this->handle($requirement));
    }
}
