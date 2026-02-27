<?php

namespace Spectacular\Core\Actions\Features;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Models\Feature;

class DeleteFeature
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('features/{feature}/delete', static::class);
    }

    public function handle(Feature $feature): void
    {
        $feature->delete();
    }

    public function asController(Feature $feature): Response
    {
        $this->handle($feature);

        return response()->noContent();
    }
}
