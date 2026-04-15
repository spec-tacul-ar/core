<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Contributor;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContributorPolicy
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
     * @param  \App\Models\Contributor  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Contributor $contributor)
    {
        return $account->canView($contributor, 'contributors');
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Contributor  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Contributor $contributor)
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
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Contributor  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Contributor $contributor)
    {
        return $this->update($account, $contributor);
    }
}
