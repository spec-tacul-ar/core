<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Feature;

class FeaturePolicy
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
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, Feature $feature)
    {
        return $account->canView($feature, 'features');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, Feature $feature)
    {
        return $account->canEdit($feature, 'features');
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, Feature $feature)
    {
        return $account->canEdit($feature, 'features');
    }
}
