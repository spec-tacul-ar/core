<?php

namespace App\Actions\Projects;

use App\Http\Resources\ProjectResource;
use App\Models\Account;
use App\Models\Project;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowProject
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('view', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->get('projects/{project}', static::class);
    }

    public function handle(Project $project, bool $hydrate, ?Account $account = null): Project
    {
        if ($hydrate) {
            $project->load([
                'actors',
                'collaborations' => fn($query) => $query->forAccount($account),
                'features',
                'features.requirements',
                'features.requirements.assignments',
                'features.requirements.tasks',
                'features.requirements.unknowns',
            ]);
        }

        return $project;
    }

    public function asController(ActionRequest $request, Project $project): ProjectResource
    {
        $hydrate = ($request->has('hydrate') && $request->input('hydrate', true))
            || ($request->has('hydrated') && $request->input('hydrated', true));

        $project = $this->handle($project, $hydrate, $request->user());

        return new ProjectResource($project);
    }
}
