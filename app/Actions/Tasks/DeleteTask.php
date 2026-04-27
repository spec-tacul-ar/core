<?php

namespace App\Actions\Tasks;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Task;

class DeleteTask
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('task'));
    }

    public static function routes(Router $router): void
    {
        $router->post('tasks/{task}/delete', static::class);
    }

    public function handle(Task $task): void
    {
        $task->delete();
    }

    public function asController(Task $task): Response
    {
        $this->handle($task);

        return response()->noContent();
    }
}
