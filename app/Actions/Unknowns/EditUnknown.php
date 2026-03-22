<?php

namespace App\Actions\Unknowns;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\UnknownResource;
use App\Models\Unknown;

class EditUnknown
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('unknown'));
    }

    public static function routes(Router $router): void
    {
        $router->post('unknowns/{unknown}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function handle(Unknown $unknown, array $validated): Unknown
    {
        $unknown->update($validated);

        return $unknown;
    }

    public function asController(ActionRequest $request, Unknown $unknown): UnknownResource
    {
        return new UnknownResource($this->handle($unknown, $request->validated()));
    }
}
