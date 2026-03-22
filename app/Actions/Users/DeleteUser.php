<?php

namespace App\Actions\Users;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\User;

class DeleteUser
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('user'));
    }

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
