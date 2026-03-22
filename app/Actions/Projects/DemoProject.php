<?php

namespace App\Actions\Projects;

use App\Enums\Role;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Rules\SluggableName as SluggableNameRule;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DemoProject
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Project::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/demo', static::class);
    }

    public function handle(array $validated): Project
    {
        $json = file_get_contents(storage_path('example_project.json'));

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $project = Project::import($data);

        auth()->user()->projects()->attach($project, ['role' => Role::OWNER]);

        return $project;
    }

    public function asController(ActionRequest $request): ProjectResource
    {
        return new ProjectResource($this->handle($request->validated()));
    }
}
