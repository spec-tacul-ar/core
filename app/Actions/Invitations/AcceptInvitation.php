<?php

namespace App\Actions\Invitations;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\Role;
use App\Models\Invitation;
use App\Notifications\InvitationAccepted;

class AcceptInvitation
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('invitations/{invitation}/accept', static::class);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('invitation'));
    }

    public function handle(Request $request, Invitation $invitation): void
    {
        if ($invitation->role === Role::OWNER) {
            abort(403);
        }

        $contributor = $invitation->project->contributors()
            ->make(['role' => $invitation->role])
            ->account()->associate($request->user());

        $contributor->save();

        $invitation->account->notify(new InvitationAccepted($contributor));

        $invitation->delete();
    }

    public function asController(Request $request, Invitation $invitation): Response
    {
        $this->handle($request, $invitation);

        return response()->noContent();
    }
}
