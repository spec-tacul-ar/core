<?php

namespace Tests\Concerns;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Collaboration;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Task;
use App\Models\Unknown;
use App\Models\Actor;
use Laravel\Passport\Passport;
use Laravel\Passport\TransientToken;

trait BuildsApiFixtures
{
    protected function actingAsAccount(?Account $account = null, ?array $scopes = null): Account
    {
        $account ??= Account::factory()->create();

        $scopes === null
            ? $this->actingAs($account->withAccessToken(new TransientToken), 'api')
            : Passport::actingAs($account, $scopes);

        return $account;
    }

    protected function attachCollaboration(Account $account, Project $project, Role $role = Role::OWNER): Collaboration
    {
        return Collaboration::factory()
            ->for($account)
            ->for($project)
            ->create(['role' => $role]);
    }

    protected function createProjectFixture(Role $role = Role::OWNER, ?Account $account = null): array
    {
        $account ??= Account::factory()->create();

        $project = Project::factory()->create();
        $collaboration = $this->attachCollaboration($account, $project, $role);
        $projectActor = Actor::factory()->for($project)->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();
        $assignment = $requirement->assignments()->create(['actor_id' => $projectActor->id]);
        $task = Task::factory()->for($requirement)->create(['is_complete' => false]);
        $unknown = Unknown::factory()->for($requirement)->create();

        return compact(
            'account',
            'assignment',
            'collaboration',
            'feature',
            'project',
            'projectActor',
            'requirement',
            'task',
            'unknown',
        );
    }

    protected function interpolatePlaceholders(mixed $value, array $bindings): mixed
    {
        if (is_array($value)) {
            return array_map(
                fn(mixed $item) => $this->interpolatePlaceholders($item, $bindings),
                $value,
            );
        }

        if (!is_string($value)) {
            return $value;
        }

        $replacements = [];

        foreach ($bindings as $key => $binding) {
            $replacements['{' . $key . '}'] = (string) $binding;
        }

        return strtr($value, $replacements);
    }
}
