<?php

namespace App\Actions\Tasks;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\TaskResource;
use App\Models\Requirement;
use App\Models\Task;
use App\Rules\QuarterHour as QuarterHourRule;
use App\Rules\Authorised;

class AddTask
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Task::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('tasks/add', static::class)
            ->middleware('sqids:requirement_id');
    }

    public function rules(): array
    {
        return [
            'requirement_id' => ['required', 'integer', new Authorised('update', Requirement::class)],
            'name' => ['required', 'string', 'max:250'],
            'estimate' => ['nullable', 'numeric', 'min:0', 'max:1000', new QuarterHourRule()],
            'is_complete' => ['required', 'boolean'],
            'weight' => ['nullable', 'integer', 'between:0,250'],
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
