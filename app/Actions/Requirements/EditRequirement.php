<?php

namespace App\Actions\Requirements;

use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\RequirementResource;
use App\Models\Feature;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
use App\Models\Actor;
use App\Rules\Authorised;
use App\Rules\QuarterHour as QuarterHourRule;
use App\Rules\SharesRelation;

class EditRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): GateResponse
    {
        return Gate::inspect('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/edit', static::class)
            ->middleware('sqids:feature_id,actor_ids.*,unknowns.*.id,tasks.*.id');
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if (!$request->has('feature_id')) {
            $request->merge(['feature_id' => $request->route('requirement')->feature_id]);
        }
    }

    public function rules(): array
    {
        return [
            'blocked_reason' => ['sometimes', 'nullable', 'string', 'max:250'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'feature_id' => ['sometimes', 'bail', 'required', 'integer', new Authorised('update', Feature::class)],
            'name' => ['sometimes', 'required', 'string', 'max:250'],
            'unknowns' => ['sometimes', 'array'],
            'unknowns.*.id' => ['sometimes', 'bail', 'required', 'integer', new Authorised('update', Unknown::class)],
            'unknowns.*.name' => ['required', 'string', 'max:250'],
            'actor_ids' => ['sometimes', 'array'],
            'actor_ids.*' => ['integer', new SharesRelation(Actor::class, 'feature_id', 'project.features'), new Authorised('update', Actor::class)],
            'tasks' => ['sometimes', 'array'],
            'tasks.*.id' => ['sometimes', 'bail', 'required', 'integer', new Authorised('update', Task::class)],
            'tasks.*.estimate' => ['nullable', 'numeric', 'min:0', 'max:1000', new QuarterHourRule()],
            'tasks.*.is_complete' => ['nullable', 'boolean'],
            'tasks.*.name' => ['required', 'string', 'max:250'],
            'tasks.*.weight' => ['nullable', 'integer', 'min:0', 'max:250'],
            'source' => ['sometimes', 'nullable', 'string', 'max:250'],
            'weight' => ['nullable', 'integer', 'between:0,250'],
        ];
    }

    public function handle(Requirement $requirement, array $validated): Requirement
    {
        $requirement->update($validated);

        if (array_key_exists('actor_ids', $validated)) {
            $requirement->assignments->whereNotIn('actor_id', $validated['actor_ids'])->each->delete();

            foreach ($validated['actor_ids'] as $actorId) {
                $requirement->assignments()->updateOrCreate(['actor_id' => $actorId]);
            }

            $requirement->load('assignments');
        }

        if (array_key_exists('unknowns', $validated)) {
            $remainingKeys = array_column($validated['unknowns'], 'id');

            $requirement->unknowns->except($remainingKeys)->each->delete();

            foreach ($validated['unknowns'] as $unknown) {
                $requirement->unknowns()->updateOrCreate(
                    ['id' => $unknown['id'] ?? null],
                    $unknown,
                );
            }

            $requirement->load('unknowns');
        }

        if (array_key_exists('tasks', $validated)) {
            $remainingKeys = array_column($validated['tasks'], 'id');

            if ($remainingKeys) {
                $requirement->tasks->except($remainingKeys)->each->delete();
            }

            foreach ($validated['tasks'] as $task) {
                $requirement->tasks()->updateOrCreate(
                    ['id' => $task['id'] ?? null],
                    $task,
                );
            }

            $requirement->load('tasks');
        }

        return $requirement;
    }

    public function asController(ActionRequest $request, Requirement $requirement): RequirementResource
    {
        return new RequirementResource($this->handle($requirement, $request->validated()));
    }
}
