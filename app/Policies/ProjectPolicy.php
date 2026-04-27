<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Project;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function create(Account $account): Response
    {
        return Response::allow();
    }

    /**
     * Determine whether the Account can view the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Project $project): Response
    {
        return $account->canView($project)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Project $project): Response
    {
        if ($this->view($account, $project)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($project)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Project $project): Response
    {
        if ($this->view($account, $project)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->owns($project)
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can create invitations.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function invite(Account $account, Project $project): Response
    {
        if ($this->view($account, $project)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->owns($project)
            ? Response::allow()
            : Response::deny();
    }
}
