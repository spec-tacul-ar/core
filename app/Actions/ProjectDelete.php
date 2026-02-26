<?php

namespace Spectacular\Core\Actions;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Project;

class ProjectDelete
{
    use AsAction;

    public function handle(Project $project): void
    {
        $project->delete();
    }

    public function asController(Project $project): Response
    {
        $this->handle($project);

        return response()->noContent();
    }
}
