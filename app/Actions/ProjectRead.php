<?php

namespace Spectacular\Core\Actions;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\ProjectResource;
use Spectacular\Core\Models\Project;

class ProjectRead
{
    use AsAction;

    public function handle(Project $project, bool $hydrate): Project
    {
        if ($hydrate) {
            $project->loadAll();
        }

        return $project;
    }

    public function asController(ActionRequest $request, Project $project): ProjectResource
    {
        return new ProjectResource(
            $this->handle($project, $request->has('hydrated') && $request->input('hydrated', true))
        );
    }
}
