<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Contributor;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContributorPolicy
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
     * @param  \App\Models\Contributor  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Contributor $contributor): Response
    {
        return $account->canView($contributor, 'contributors')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Contributor  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Contributor $contributor): Response
    {
        if ($this->view($account, $contributor)->denied()) {
            return Response::denyAsNotFound();
        }

        if ($contributor->project->isArchived()) {
            return Response::deny();
        }

        $owners = $contributor->project->contributors()
            ->where('role', Role::OWNER)
            ->get();

        $me = $owners->firstWhere('account_id', $account->id);

        if (!$me) {
            return Response::deny();
        }

        if ($contributor->role === Role::OWNER) {
            if ($owners->count() === 1) {
                return Response::deny();
            }

            if ($contributor->updated_at->isBefore($me->updated_at)) {
                return Response::deny();
            }

            if ($contributor->updated_at->equalTo($me->updated_at) && $contributor->isNot($me)) {
                return Response::deny();
            }
        }

        return Response::allow();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Contributor  $contributor
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Contributor $contributor): Response
    {
        if ($this->view($account, $contributor)->denied()) {
            return Response::denyAsNotFound();
        }

        // Get my role in this project
        $me = $contributor->project->contributors()->whereBelongsTo($account)->first();

        // If I'm not in this project, reject.
        if (!$me) {
            return Response::deny();
        }

        if ($contributor->project->isArchived() && $contributor->isNot($me)) {
            return Response::deny();
        }

        // If I'm an owner
        if ($me->role === Role::OWNER) {
            // If it's me, check there's another owner to take my place
            if ($contributor->is($me)) {
                return $contributor->project->contributors()->where('role', Role::OWNER)->count() > 1
                    ? Response::allow()
                    : Response::deny();
            }

            // If the other user is an owner and older than us, reject.
            if ($contributor->role === Role::OWNER && $contributor->updated_at->isBefore($me->updated_at)) {
                return Response::deny();
            }

            // I'm an owner so I can delete them.
            return Response::allow();
        }

        // You can always remove yourself if you're not an owner
        return $contributor->account->is($account)
            ? Response::allow()
            : Response::deny();
    }
}
