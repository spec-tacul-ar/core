<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Requirement;

class RequirementPolicy
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
     * @param  \App\Models\Requirement  $requirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, Requirement $requirement)
    {
        return $account->canView($requirement, 'features.requirements');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Requirement  $requirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, Requirement $requirement)
    {
        return $account->canEdit($requirement, 'features.requirements');
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Requirement  $requirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, Requirement $requirement)
    {
        return $account->canEdit($requirement, 'features.requirements');
    }
}
