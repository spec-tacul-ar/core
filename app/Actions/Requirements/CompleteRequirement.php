<?php

namespace App\Actions\Requirements;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Requirement;

class CompleteRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/complete', static::class);
    }

    public function handle(Requirement $requirement): Collection
    {
        return $requirement->tasks
            ->each->complete()
            ->map(fn($task) => [
                'id' => $task->sqid,
                'is_complete' => $task->is_complete,
            ]);
    }

    public function asController(Requirement $requirement): JsonResource
    {
        $tasks = $this->handle($requirement);

        return new JsonResource($tasks);
    }
}
