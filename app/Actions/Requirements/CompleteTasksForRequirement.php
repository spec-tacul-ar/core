<?php

namespace App\Actions\Requirements;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Requirement;

class CompleteTasksForRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/tasks/complete', static::class);
    }

    public function handle(Requirement $requirement): Collection
    {
        return $requirement->tasks
            ->each->complete()
            ->map->only('id', 'is_complete');
    }

    public function asController(Requirement $requirement): JsonResource
    {
        return new JsonResource($this->handle($requirement));
    }
}
