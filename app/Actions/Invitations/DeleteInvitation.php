<?php

namespace App\Actions\Invitations;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Invitation;

class DeleteInvitation
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('invitations/{invitation}/delete', static::class);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('invitation'));
    }

    public function handle(Invitation $invitation): void
    {
        $invitation->delete();
    }

    public function asController(Invitation $invitation): Response
    {
        $this->handle($invitation);

        return response()->noContent();
    }
}
