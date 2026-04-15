<?php

namespace App\Actions\Requirements;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\RequirementResource;
use App\Models\Feature;
use App\Models\Requirement;
use App\Models\Actor;
use App\Rules\QuarterHour as QuarterHourRule;
use App\Rules\SharesRelation;
use Spatie\ValidationRules\Rules\Authorized;

class AddRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('create', Requirement::class);
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/add', static::class);
    }

    public function rules(): array
    {
        return [
            'blocked_reason' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'feature_id' => ['required', 'integer', new Authorized('update', Feature::class)],
            'name' => ['required', 'string', 'max:255'],
            'unknowns' => ['nullable', 'array'],
            'unknowns.*.name' => ['required', 'string', 'max:255'],
            'actor_ids' => ['array'],
            'actor_ids.*' => ['integer', new SharesRelation(Actor::class, 'feature_id', 'project.features'), new Authorized('update', Actor::class)],
            'source' => ['nullable', 'string', 'max:255'],
            'tasks' => ['nullable', 'array'],
            'tasks.*.estimate' => ['nullable', 'numeric', new QuarterHourRule()],
            'tasks.*.is_complete' => ['nullable', 'boolean'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(array $validated): Requirement
    {
        $requirement = Requirement::create($validated);

        if (!empty($validated['actor_ids'])) {
            $requirement->assignments()->createMany(array_map(fn($actorId) => ['actor_id' => $actorId], $validated['actor_ids']));
        }

        if (!empty($validated['unknowns'])) {
            $requirement->unknowns()->createMany($validated['unknowns']);
        }

        if (!empty($validated['tasks'])) {
            $requirement->tasks()->createMany($validated['tasks']);
        }

        // We have to fetch a new copy because the reference is set after create.
        return $requirement->fresh(['assignments', 'unknowns', 'tasks']);
    }

    public function asController(ActionRequest $request): RequirementResource
    {
        return new RequirementResource($this->handle($request->validated()));
    }
}
