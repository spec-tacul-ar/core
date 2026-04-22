<?php

namespace App\Actions\Tasks;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Rules\QuarterHour as QuarterHourRule;

class EditTask
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('task'));
    }

    public static function routes(Router $router): void
    {
        $router->post('tasks/{task}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:250'],
            'estimate' => ['sometimes', 'nullable', 'numeric', new QuarterHourRule()],
            'is_complete' => ['sometimes', 'required', 'boolean'],
            'weight' => ['nullable', 'integer', 'between:0,250'],
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
