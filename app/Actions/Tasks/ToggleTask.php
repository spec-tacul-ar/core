<?php

namespace App\Actions\Tasks;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ToggleTask
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('task'));
    }

    public static function routes(Router $router): void
    {
        $router->post('tasks/{task}/toggle', static::class);
    }

    public function rules(): array
    {
        return [
            'is_complete' => ['sometimes', 'boolean'],
        ];
    }

    public function handle(Task $task, bool $isComplete = true): Task
    {
        $task->update(['is_complete' => $isComplete]);

        return $task;
    }

    public function asController(ActionRequest $request, Task $task): TaskResource
    {
        $task = $this->handle($task, $request->boolean('is_complete', true));

        return new TaskResource($task);
    }
}
