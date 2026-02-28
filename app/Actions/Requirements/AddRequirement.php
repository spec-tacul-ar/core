<?php

namespace Spectacular\Core\Actions\Requirements;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\RequirementResource;
use Spectacular\Core\Models\Requirement;
use Spectacular\Core\Models\User;
use Spectacular\Core\Rules\QuarterHour as QuarterHourRule;
use Spectacular\Core\Rules\SharesRelation;

class AddRequirement
{
    use AsAction;

    public static function routes(Router $router): void
    {
        $router->post('requirements/add', static::class);
    }

    public function rules(): array
    {
        return [
            'blocked_reason' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'feature_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'unknowns' => ['nullable', 'array'],
            'unknowns.*.name' => ['required', 'string', 'max:255'],
            'user_ids' => ['array'],
            'user_ids.*' => ['integer', new SharesRelation(User::class, 'feature_id', 'project')],
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

        if (!empty($validated['user_ids'])) {
            $requirement->assignments()->createMany(array_map(fn ($userId) => ['user_id' => $userId], $validated['user_ids']));
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
