<?php

namespace App\Actions\Projects;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Project;

class DeleteProject
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/{project}/delete', static::class);
    }

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
