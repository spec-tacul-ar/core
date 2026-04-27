<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Actor;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActorPolicy
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
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Actor $actor): Response
    {
        return $account->canView($actor, 'actors')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Actor $actor): Response
    {
        if ($this->view($account, $actor)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($actor, 'actors')
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Actor  $actor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Actor $actor): Response
    {
        if ($this->view($account, $actor)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->canEdit($actor, 'actors')
            ? Response::allow()
            : Response::deny();
    }
}
