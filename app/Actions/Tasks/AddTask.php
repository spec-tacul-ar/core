<?php

namespace App\Actions\Tasks;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\TaskResource;
use App\Models\Requirement;
use App\Models\Task;
use App\Rules\QuarterHour as QuarterHourRule;
use Spatie\ValidationRules\Rules\Authorized;

class AddTask
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Task::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('tasks/add', static::class);
    }

    public function rules(): array
    {
        return [
            'requirement_id' => ['required', 'integer', new Authorized('update', Requirement::class)],
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
