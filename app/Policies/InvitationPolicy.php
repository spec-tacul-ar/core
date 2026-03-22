<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class InvitationPolicy
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
     * @param  mixed  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, mixed $invitation)
    {
        return $account->owns($invitation, 'invitations')
            || $account->email === $invitation->email;
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  mixed  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, mixed $invitation)
    {
        return $account->email === $invitation->email;
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  mixed  $invitation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, mixed $invitation)
    {
        return $account->owns($invitation, 'invitations')
            || $account->email === $invitation->email;
    }
}
