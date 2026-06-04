<?php

namespace App\Actions\Projects;

use App\Enums\Role;
use App\Http\Resources\ProjectResource;
use App\Models\Account;
use App\Models\Project;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DemoProject
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Project::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/demo', static::class);
    }

    public function handle(Account $account): Project
    {
        $json = file_get_contents(resource_path('example_project.json'));

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $project = Project::import($data, false);

        $project->addCollaboration($account, Role::OWNER);

        return $project;
    }

    public function asController(ActionRequest $request): ProjectResource
    {
        $project = $this->handle($request->user());

        return new ProjectResource($project);
    }
}
