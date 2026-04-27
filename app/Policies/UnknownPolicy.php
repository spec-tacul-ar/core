<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Unknown;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class UnknownPolicy
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
     * @param  \App\Models\Unknown  $unknown
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Unknown $unknown): Response
    {
        return $account->canView($unknown, 'features.requirements.unknowns')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Unknown  $unknown
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Unknown $unknown): Response
    {
        if ($this->view($account, $unknown)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($unknown, 'features.requirements.unknowns')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Unknown  $unknown
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Unknown $unknown): Response
    {
        if ($this->view($account, $unknown)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($unknown, 'features.requirements.unknowns')
            ? Response::allow()
            : Response::deny();
    }
}
