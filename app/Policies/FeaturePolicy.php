<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Feature;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeaturePolicy
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
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Feature $feature): Response
    {
        return $account->canView($feature, 'features')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Feature $feature): Response
    {
        if ($this->view($account, $feature)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($feature, 'features')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Feature  $feature
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Feature $feature): Response
    {
        if ($this->view($account, $feature)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($feature, 'features')
            ? Response::allow()
            : Response::deny();
    }
}
