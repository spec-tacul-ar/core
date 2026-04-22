<?php

namespace App\Actions\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\AccountResource;

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

    public function handle(Request $request, array $validated)
    {
        $account = $request->user();

        $account->update($validated);

        return $account;
    }

    public function asController(ActionRequest $request): AccountResource
    {
        return new AccountResource($this->handle($request, $request->validated()));
    }
}
