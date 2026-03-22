<?php

namespace App\Actions\Projects;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Project;

class ReadmarkProject
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('view', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/{project}/readmark', static::class);
    }

    public function asController(Request $request, Project $project): JsonResource
    {
        $readmark = $request->user()->markAsRead($project);

        return new JsonResource([
            'id' => $project->getKey(),
            'readmark' => $readmark->updated_at,
        ]);
    }
}
