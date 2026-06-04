<?php

namespace App\Actions\Tokens;

use App\Http\Resources\TokenResource;
use App\Models\Account;
use App\Models\Token;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\PersonalAccessTokenResult;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateToken
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Token::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('tokens', static::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
        ];
    }

    public function handle(Account $account, array $data): PersonalAccessTokenResult
    {
        return $account->createToken($data['name']);
    }

    public function asController(ActionRequest $request): TokenResource
    {
        $result = $this->handle($request->user(), $request->validated());

        return new TokenResource($result->token, $result->accessToken);
    }
}
