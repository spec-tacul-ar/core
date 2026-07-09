<?php

namespace App\Actions\Tokens;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Token;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class RevokeToken
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('token'));
    }

    public static function routes(Router $router): void
    {
        $router->post('tokens/{token}/revoke', static::class);
    }

    public function handle(Token $token): void
    {
        $token->revoke();
        $token->refreshToken?->revoke();
    }

    public function asController(Token $token): Response
    {
        $this->handle($token);

        return response()->noContent();
    }
}
