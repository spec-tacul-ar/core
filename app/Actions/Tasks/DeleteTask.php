<?php

namespace Spectacular\Core\Actions\Tasks;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Task;

class DeleteTask
{
    use AsAction;

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
