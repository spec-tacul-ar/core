<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\Assignment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AssignmentPolicy
{
    use HandlesAuthorization;

    public function view(Account $account, Assignment $assignment): Response
    {
        return $account->canView($assignment, 'features.requirements.assignments')
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
