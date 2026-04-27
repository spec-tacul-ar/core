<?php

namespace App\Actions\Projects;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\ProjectResource;
use App\Models\Project;

class ReadProject
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('view', $request->route('project'));
    }

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
            $this->handle($project, $request->has('hydrated') && $request->input('hydrated', true)),
        );
    }
}
