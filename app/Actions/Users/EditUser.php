<?php

namespace Spectacular\Core\Actions\Users;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\UserResource;
use Spectacular\Core\Models\User;

class EditUser
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('users/{user}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'summary' => ['nullable', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(User $user, array $validated): User
    {
        $user->update($validated);

        return $user;
    }

    public function asController(ActionRequest $request, User $user): UserResource
    {
        return new UserResource($this->handle($user, $request->validated()));
    }
}
