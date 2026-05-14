<?php

namespace App\Actions\Projects;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UnarchiveProject
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/{project}/unarchive', static::class);
    }

    public function handle(Project $project): Project
    {
        return $project->unarchive();
    }

    public function asController(Project $project): ProjectResource
    {
        return new ProjectResource($this->handle($project));
    }
}
