<?php

namespace App\Actions\Features;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Feature;

class DeleteFeature
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('feature'));
    }

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
