<?php

namespace App\Actions\Invitations;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Invitation;

class AcceptInvitation
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('invitations/{invitation}/accept', static::class);
    }

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('invitation'));
    }

    public function handle(Request $request, Invitation $invitation): void
    {
        $collaboration = $invitation->project->collaborations()
            ->make(['role' => $invitation->role])
            ->account()->associate($request->user());

        $collaboration->save();

        // Disabled, for now.
        // $invitation->account->notify(new InvitationAccepted($collaboration));

        $invitation->delete();
    }

    public function asController(Request $request, Invitation $invitation): Response
    {
        $this->handle($request, $invitation);

        return response()->noContent();
    }
}
