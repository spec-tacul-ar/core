<?php

namespace Spectacular\Core\Actions;

use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\User;

class UserDelete
{
    use AsAction;

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
