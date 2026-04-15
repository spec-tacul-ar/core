<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Requirement;
use Illuminate\Auth\Access\HandlesAuthorization;

class RequirementPolicy
{
    use HandlesAuthorization;

    public function create(Account $account): bool
    {
        return true;
    }

    /**
     * Determine whether the Account can view the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Requirement  $requirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Requirement $requirement)
    {
        return $account->canView($requirement, 'features.requirements');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Requirement  $requirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Requirement $requirement)
    {
        return $account->canEdit($requirement, 'features.requirements');
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Requirement  $requirement
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Requirement $requirement)
    {
        return $account->canEdit($requirement, 'features.requirements');
    }
}
