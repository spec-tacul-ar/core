<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Unknown;

class UnknownPolicy
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
     * @param  \App\Models\Unknown  $unknown
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, Unknown $unknown)
    {
        return $account->canView($unknown, 'features.requirements.unknowns');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Unknown  $unknown
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, Unknown $unknown)
    {
        return $account->canEdit($unknown, 'features.requirements.unknowns');
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Unknown  $unknown
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, Unknown $unknown)
    {
        return $account->canEdit($unknown, 'features.requirements.unknowns');
    }
}
