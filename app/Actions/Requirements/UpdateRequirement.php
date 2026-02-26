<?php

namespace Spectacular\Core\Actions\Requirements;

use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Spectacular\Core\Http\Resources\RequirementResource;
use Spectacular\Core\Models\Requirement;
use Spectacular\Core\Rules\QuarterHour as QuarterHourRule;

class UpdateRequirement
{
    use AsAction;

    public function rules(): array
    {
        return [
            'blocked_reason' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'feature_id' => ['sometimes', 'bail', 'required', 'integer'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'unknowns' => ['sometimes', 'array'],
            'unknowns.*.id' => ['sometimes', 'bail', 'required', 'integer'],
            'unknowns.*.name' => ['required', 'string', 'max:255'],
            'user_ids' => ['sometimes', 'array'],
            'user_ids.*' => ['integer'],
            'tasks' => ['sometimes', 'array'],
            'tasks.*.id' => ['sometimes', 'bail', 'required', 'integer'],
            'tasks.*.estimate' => ['nullable', 'numeric', new QuarterHourRule()],
            'tasks.*.is_complete' => ['nullable', 'boolean'],
            'tasks.*.name' => ['required', 'string', 'max:255'],
            'tasks.*.weight' => ['nullable', 'integer', 'min:0', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'integer', 'between:0,255'],
        ];
    }

    public function handle(Requirement $requirement, array $validated): Requirement
    {
        // TODO: Make sure the user_id is within the same project as the feature_id
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
