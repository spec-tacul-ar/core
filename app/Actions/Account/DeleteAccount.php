<?php

namespace App\Actions\Account;

use App\Models\Account;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteAccount
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('account/delete', static::class);
    }

    public function rules(): array
    {
        return [
            'confirmation' => ['required', 'accepted'],
        ];
    }

    public function handle(Account $account): void
    {
        $account->delete();
    }

    public function asController(ActionRequest $request): Response
    {
        $this->handle($request->user());

        return response()->noContent();
    }
}
