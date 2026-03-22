<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Task;

class TaskPolicy
{
    use HandlesAuthorization;

    public function create(mixed $account): bool
    {
        return true;
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, Task $task)
    {
        return $account->canView($task, 'features.requirements.tasks');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, Task $task)
    {
        return $account->canEdit($task, 'features.requirements.tasks');
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, Task $task)
    {
        return $account->canEdit($task, 'features.requirements.tasks');
    }
}
