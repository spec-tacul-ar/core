<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Invitation;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
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
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Invitation $invitation)
    {
        return $account->owns($invitation, 'invitations')
            || $account->email === $invitation->email;
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Invitation $invitation)
    {
        return $account->email === $invitation->email && $account->hasVerifiedEmail();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Invitation  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Invitation $invitation)
    {
        if ($account->owns($invitation, 'invitations')) {
            return true;
        }

        return $account->email === $invitation->email && $account->hasVerifiedEmail();
    }
}
