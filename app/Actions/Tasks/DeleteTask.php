<?php

namespace Spectacular\Core\Actions\Tasks;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Task;

class DeleteTask
{
    use AsAction;

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
