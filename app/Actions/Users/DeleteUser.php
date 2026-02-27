<?php

namespace Spectacular\Core\Actions\Users;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\User;

class DeleteUser
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('users/{user}/delete', static::class);
    }

    public function handle(User $user): void
    {
        $user->delete();
    }

    public function asController(User $user): Response
    {
        $this->handle($user);

        return response()->noContent();
    }
}
