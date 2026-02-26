<?php

namespace Spectacular\Core\Actions\Projects;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Project;

class DeleteProject
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
