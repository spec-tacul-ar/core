<?php

namespace App\Actions\Unknowns;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Unknown;

class DeleteUnknown
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('unknown'));
    }

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
