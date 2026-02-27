<?php

namespace Spectacular\Core\Actions\Unknowns;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Unknown;

class DeleteUnknown
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('unknowns/{unknown}/delete', static::class);
    }

    public function handle(Unknown $unknown): void
    {
        $unknown->delete();
    }

    public function asController(Unknown $unknown): Response
    {
        $this->handle($unknown);

        return response()->noContent();
    }
}
