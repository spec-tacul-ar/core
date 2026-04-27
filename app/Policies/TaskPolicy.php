<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Task;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function create(Account $account): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Task $task): Response
    {
        return $account->canView($task, 'features.requirements.tasks')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Task $task): Response
    {
        if ($this->view($account, $task)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($task, 'features.requirements.tasks')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Task $task): Response
    {
        if ($this->view($account, $task)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($task, 'features.requirements.tasks')
            ? Response::allow()
            : Response::deny();
    }
}
