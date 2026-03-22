<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\Enums\Role;

class ContributorPolicy
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
     * @param  mixed  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(mixed $account, mixed $contributor)
    {
        return $account->canView($contributor, 'contributors');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  mixed  $account
     * @param  mixed  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(mixed $account, mixed $contributor)
    {
        $owners = $contributor->project->contributors()
            ->where('role', Role::OWNER)
            ->get();

        $me = $owners->firstWhere('account_id', $account->id);

        if (!$me) {
            return false;
        }

        if ($contributor->role === Role::OWNER) {
            if ($owners->hasSole()) {
                return false;
            }

            if ($contributor->updated_at->isBefore($me->updated_at)) {
                return false;
            }

            if ($contributor->updated_at->equalTo($me->updated_at) && $contributor->isNot($me)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  mixed  $account
     * @param  mixed  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(mixed $account, mixed $contributor)
    {
        return $this->update($account, $contributor);
    }
}
