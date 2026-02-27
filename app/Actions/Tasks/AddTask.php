<?php

namespace Spectacular\Core\Actions\Tasks;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\TaskResource;
use Spectacular\Core\Models\Task;
use Spectacular\Core\Rules\QuarterHour as QuarterHourRule;

class AddTask
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('tasks/add', static::class);
    }

    public function rules(): array
    {
        return [
            'requirement_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'estimate' => ['nullable', 'numeric', new QuarterHourRule()],
            'is_complete' => ['required', 'boolean'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(array $validated): Task
    {
        return Task::create($validated);
    }

    public function asController(ActionRequest $request): TaskResource
    {
        return new TaskResource($this->handle($request->validated()));
    }
}
