<?php

namespace App\Actions\Requirements;

use App\Http\Resources\RequirementResource;
use App\Models\Requirement;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class AppendToRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/append', static::class);
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator, ActionRequest $request): void
    {
        $validator->after(function (Validator $validator) use ($request) {
            $requirement = $request->route('requirement');
            $text = $request->input('text');

            if (!is_string($text)) {
                return;
            }

            if (strlen($this->descriptionWithAppendage($requirement, $text)) > 10000) {
                $validator->errors()->add('text', 'The description may not be greater than 10000 characters.');
            }
        });
    }

    public function handle(Requirement $requirement, array $validated): Requirement
    {
        $requirement->update([
            'description' => $this->descriptionWithAppendage($requirement, $validated['text']),
        ]);

        return $requirement;
    }

    public function asController(ActionRequest $request, Requirement $requirement): RequirementResource
    {
        $validated = $request->validated();

        $requirement = $this->handle($requirement, $validated);

        return new RequirementResource($requirement);
    }

    private function descriptionWithAppendage(Requirement $requirement, string $text): string
    {
        return $requirement->description . '<p>' . e($text) . '</p>';
    }
}
