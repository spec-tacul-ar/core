<?php

namespace App\Actions\Projects;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Account;
use App\Models\Project;
use Carbon\Carbon;

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

    public function handle(Account $account, Project $project): Carbon
    {
        $timestamp = now();

        $account->markAsRead($project, $timestamp);

        return $timestamp;
    }

    public function asController(Request $request, Project $project): JsonResource
    {
        $timestamp = $this->handle($request->user(), $project);

        return new JsonResource($timestamp);
    }
}
