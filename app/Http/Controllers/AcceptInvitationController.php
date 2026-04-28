<?php

namespace App\Http\Controllers;

use App\Actions\Invitations\AcceptInvitation;
use App\Models\Account;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AcceptInvitationController extends Controller
{
    public function __invoke(Request $request, Invitation $invitation): RedirectResponse
    {
        // This route is signed so we can verify the account without auth.
        $account = Account::findByEmail($invitation->email);

        if ($account && !$account->hasVerifiedEmail()) {
            $account->markEmailAsVerified();
        }

        // Redirect the user to login, if unauthenticated. They'll see the pending invite on their dashboard.
        if (!$request->user()) {
            if ($account) {
                return redirect(config('spectacular.path') . '/login');
            }

            return redirect(config('spectacular.path') . '/register');
        }

        // We verified our user. $request will need to know.
        $request->user()->refresh();

        // Check the user can accept this invitation.
        Gate::authorize('update', $invitation);

        // Accept the invitation
        AcceptInvitation::run($request, $invitation);

        return redirect($invitation->project->url);
    }
}
