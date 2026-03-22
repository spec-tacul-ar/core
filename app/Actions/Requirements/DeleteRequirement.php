<?php

namespace App\Actions\Requirements;

use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Requirement;

class DeleteRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('delete', $request->route('requirement'));
    }

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
