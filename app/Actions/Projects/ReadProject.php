<?php

namespace Spectacular\Core\Actions\Projects;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\ProjectResource;
use Spectacular\Core\Models\Project;

class ReadProject
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('projects/{project}/read', static::class);
    }

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
