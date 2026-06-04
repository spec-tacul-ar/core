<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Collaboration;
use Illuminate\Auth\Access\Response;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollaborationPolicy
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
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Account $account, Collaboration $collaboration): Response
    {
        return $account->canView($collaboration, 'collaborations')
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the Account can update the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Account $account, Collaboration $collaboration): Response
    {
        if ($this->view($account, $collaboration)->denied()) {
            return Response::denyAsNotFound();
        }

        if ($collaboration->project->isArchived()) {
            return Response::deny();
        }

        $owners = $collaboration->project->collaborations()
            ->where('role', Role::OWNER)
            ->get();

        $me = $owners->firstWhere('account_id', $account->id);

        if (!$me) {
            return Response::deny();
        }

        if ($collaboration->role === Role::OWNER) {
            if ($owners->count() === 1) {
                return Response::deny();
            }

            if ($collaboration->updated_at->isBefore($me->updated_at)) {
                return Response::deny();
            }

            if ($collaboration->updated_at->equalTo($me->updated_at) && $collaboration->isNot($me)) {
                return Response::deny();
            }
        }

        return Response::allow();
    }

    /**
     * Determine whether the Account can delete the model.
     *
     * @param  \App\Models\Account  $account
     * @param  \App\Models\Collaboration  $collaboration
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Account $account, Collaboration $collaboration): Response
    {
        if ($this->view($account, $collaboration)->denied()) {
            return Response::denyAsNotFound();
        }

        // Get my role in this project
        $me = $collaboration->project->collaborations()->whereBelongsTo($account)->first();

        // If I'm not in this project, reject.
        if (!$me) {
            return Response::deny();
        }

        if ($collaboration->project->isArchived() && $collaboration->isNot($me)) {
            return Response::deny();
        }

        // If I'm an owner
        if ($me->role === Role::OWNER) {
            // If it's me, check there's another owner to take my place
            if ($collaboration->is($me)) {
                return $collaboration->project->collaborations()->where('role', Role::OWNER)->count() > 1
                    ? Response::allow()
                    : Response::deny();
            }

            // If the other user is an owner and older than us, reject.
            if ($collaboration->role === Role::OWNER && $collaboration->updated_at->isBefore($me->updated_at)) {
                return Response::deny();
            }

            // I'm an owner so I can delete them.
            return Response::allow();
        }

        // You can always remove yourself if you're not an owner
        return $collaboration->account->is($account)
            ? Response::allow()
            : Response::deny();
    }
}
