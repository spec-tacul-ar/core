<?php

namespace App\Actions\Account;

use Illuminate\Http\Request;
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

    public function handle(Request $request): void
    {
        $request->user()->delete();
    }

    public function asController(ActionRequest $request): Response
    {
        $this->handle($request);

        return response()->noContent();
    }
}
