<?php

namespace App\Actions\Account;

use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class EditAccount
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('account/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:250'],
        ];
    }

    public function handle(Account $account, array $validated)
    {
        $account->update($validated);

        return $account;
    }

    public function asController(ActionRequest $request): AccountResource
    {
        $account = $this->handle($request->user(), $request->validated());

        return new AccountResource($account);
    }
}
