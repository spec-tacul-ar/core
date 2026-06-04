<?php

namespace App\Actions\Unknowns;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\UnknownResource;
use App\Models\Requirement;
use App\Models\Unknown;
use App\Rules\Authorised;

class CreateUnknown
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('create', Unknown::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('unknowns', static::class)
            ->middleware('sqids:requirement_id');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:250'],
            'requirement_id' => ['required', 'integer', new Authorised('update', Requirement::class)],
        ];
    }

    public function handle(array $validated): Unknown
    {
        return Unknown::create($validated);
    }

    public function asController(ActionRequest $request): UnknownResource
    {
        $unknown = $this->handle($request->validated());

        return new UnknownResource($unknown);
    }
}
