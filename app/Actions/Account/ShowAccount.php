<?php

namespace App\Actions\Account;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\AccountResource;

class ShowAccount
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('account', static::class);
    }

    public function handle(Account $account): Account
    {
        return $account;
    }

    public function asController(Request $request): AccountResource
    {
        $account = $this->handle($request->user());

        return new AccountResource($account);
    }
}
