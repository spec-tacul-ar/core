<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Invitation;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
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
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Invitation $invitation): Response
    {
        if (!$account->owns($invitation, 'invitations') && $account->email !== $invitation->email) {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Invitation $invitation): Response
    {
        if ($this->view($account, $invitation)->denied()) {
            return Response::denyAsNotFound();
        }

        return $account->email === $invitation->email && $account->hasVerifiedEmail()
            ? Response::allow()
            : Response::deny();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Invitation $invitation): Response
    {
        if ($this->view($account, $invitation)->denied()) {
            return Response::denyAsNotFound();
        }

        if ($account->owns($invitation, 'invitations')) {
            return Response::allow();
        }

        return $account->email === $invitation->email && $account->hasVerifiedEmail()
            ? Response::allow()
            : Response::deny();
    }
}
