<?php

namespace Spectacular\Core\Actions\Requirements;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Requirement;

class DeleteRequirement
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/delete', static::class);
    }

    public function handle(Requirement $requirement): void
    {
        $requirement->delete();
    }

    public function asController(Requirement $requirement): Response
    {
        $this->handle($requirement);

        return response()->noContent();
    }
}
