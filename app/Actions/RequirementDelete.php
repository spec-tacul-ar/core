<?php

namespace Spectacular\Core\Actions;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Requirement;

class RequirementDelete
{
    use AsAction;

    public function handle(Requirement $requirement): void
    {
        $requirement->delete();
    }

    public function asController(Requirement $requirement): Response
    {
        $this->handle($requirement);

        return response()->noContent();
    }
}
