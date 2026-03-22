<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Project;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function create(mixed $account): bool
    {
        return true;
    }

    /**
     * Determine whether the Account can view the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, Project $project)
    {
        return $account->canView($project);
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, Project $project)
    {
        return $account->canEdit($project);
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, Project $project)
    {
        return $account->owns($project);
    }
}
