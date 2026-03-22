<?php

namespace App\Actions\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\AccountResource;

class ReadAccount
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('auth/account', static::class);
    }

    public function asController(Request $request): AccountResource
    {
        return new AccountResource($request->user());
    }
}
