<?php

namespace App\Actions\Requirements;

use Illuminate\Routing\Router;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Http\Resources\RequirementResource;
use App\Models\Feature;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
use App\Models\User;
use App\Rules\QuarterHour as QuarterHourRule;
use App\Rules\SharesRelation;
use Spatie\ValidationRules\Rules\Authorized;

class EditRequirement
{
    use AsAction;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('update', $request->route('requirement'));
    }

    public static function routes(Router $router): void
    {
        $router->post('requirements/{requirement}/edit', static::class);
    }

    public function rules(): array
    {
        return [
            'blocked_reason' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'feature_id' => ['sometimes', 'bail', 'required', 'integer', new Authorized('update', Feature::class)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'unknowns' => ['sometimes', 'array'],
            'unknowns.*.id' => ['sometimes', 'bail', 'required', 'integer', new Authorized('update', Unknown::class)],
            'unknowns.*.name' => ['required', 'string', 'max:255'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer', new SharesRelation(User::class, 'feature_id', 'project.features')],
            'tasks' => ['sometimes', 'array'],
            'tasks.*.id' => ['sometimes', 'bail', 'required', 'integer', new Authorized('update', Task::class)],
            'tasks.*.estimate' => ['nullable', 'numeric', new QuarterHourRule()],
            'tasks.*.is_complete' => ['nullable', 'boolean'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if (!$request->has('feature_id')) {
            $request->merge(['feature_id' => $request->route('requirement')->feature_id]);
        }
    }

    public function handle(Requirement $requirement, array $validated): Requirement
    {
        $requirement->update($validated);

        if (array_key_exists('user_ids', $validated)) {
            $requirement->assignments->whereNotIn('user_id', $validated['user_ids'])->each->delete();

            foreach ($validated['user_ids'] as $userId) {
                $requirement->assignments()->updateOrCreate(['user_id' => $userId]);
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
