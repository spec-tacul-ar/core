<?php

namespace App\Actions\Contributors;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Contributor;

class DeleteContributor
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('delete', $request->route('contributor'));
    }

    public static function routes(Router $router): void
    {
        $router->post('contributors/{contributor}/delete', static::class);
    }

    public function handle(Contributor $contributor): void
    {
        $contributor->delete();
    }

    public function asController(Contributor $contributor): Response
    {
        $this->handle($contributor);

        return response()->noContent();
    }
}
