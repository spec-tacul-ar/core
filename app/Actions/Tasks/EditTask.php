<?php

namespace Spectacular\Core\Actions\Tasks;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\TaskResource;
use Spectacular\Core\Models\Task;
use Spectacular\Core\Rules\QuarterHour as QuarterHourRule;

class EditTask
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('tasks/{task}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'estimate' => ['sometimes', 'nullable', 'numeric', new QuarterHourRule()],
            'is_complete' => ['sometimes', 'required', 'boolean'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(Task $task, array $validated): Task
    {
        $task->update($validated);

        return $task;
    }

    public function asController(ActionRequest $request, Task $task): TaskResource
    {
        return new TaskResource($this->handle($task, $request->validated()));
    }
}
