<?php

namespace App\Actions\Tokens;

use App\Http\Resources\TokenResource;
use App\Models\Account;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class IndexTokens
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->get('tokens', static::class);
    }

    public function handle(Account $user)
    {
        return $user->tokens()
            ->where('revoked', false)
            ->whereFuture('expires_at')
            // ->whereHas('client', fn ($query) => $query->whereJsonContains('grant_types', 'personal_access'))
            ->get();
    }

    public function asController(ActionRequest $request): ResourceCollection
    {
        $tokens = $this->handle($request->user());

        return TokenResource::collection($tokens);
    }
}