<?php

namespace App\Actions\Projects;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Project;

class ReadmarkProject
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('view', $request->route('project'));
    }

    public static function routes(Router $router): void
    {
        $router->post('projects/{project}/readmark', static::class);
    }

    public function asController(Request $request, Project $project): JsonResource
    {
        $readmark = $request->user()->markAsRead($project);

        return new JsonResource([
            'id' => $project->sqid,
            'readmark' => $readmark->updated_at,
        ]);
    }
}
