<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Actor;
use App\Models\Feature;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Requirement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class ValidationRulesTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_project_names_must_contain_at_least_one_letter(): void
    {
        $this->actingAsAccount();

        $this->postJson('/api/projects/add', [
            'name' => '12345',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_task_estimates_must_be_quarter_hour_segments(): void
    {
        $fixture = $this->createProjectFixture(Role::EDITOR);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/tasks/add', [
            'estimate' => 1.3,
            'is_complete' => false,
            'name' => 'Bad estimate',
            'requirement_id' => $fixture['requirement']->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estimate');

        $this->postJson('/api/requirements/add', [
            'feature_id' => $fixture['feature']->id,
            'name' => 'requirement with bad estimate',
            'tasks' => [
                ['estimate' => 0.3, 'is_complete' => false, 'name' => 'Bad nested estimate'],
            ],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tasks.0.estimate');
    }

    public function test_invitations_reject_duplicate_existing_member_and_own_email_addresses(): void
    {
        $project = Project::factory()->create();
        $owner = Account::factory()->create(['email' => 'owner@example.test']);
        $member = Account::factory()->create(['email' => 'member@example.test']);

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($member, $project, Role::VIEWER);

        Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create(['email' => 'pending@example.test']);

        $this->actingAsAccount($owner);

        $basePayload = [
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ];

        $this->postJson('/api/invitations/add', $basePayload + ['email' => 'pending@example.test'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/invitations/add', $basePayload + ['email' => 'member@example.test'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->postJson('/api/invitations/add', $basePayload + ['email' => 'owner@example.test'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_comments_can_target_requirements_in_the_same_project(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $fixture['requirement']->id,
            'commentable_type' => 'requirement',
            'message' => 'Requirement comment',
            'project_id' => $fixture['project']->id,
        ])->assertCreated();

        $this->assertDatabaseHas('comments', [
            'account_id' => $fixture['account']->id,
            'commentable_id' => $fixture['requirement']->id,
            'commentable_type' => 'requirement',
            'project_id' => $fixture['project']->id,
        ]);
    }

    public function test_comments_reject_commentables_from_other_projects(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $otherProject = Project::factory()->create();
        $otherFeature = Feature::factory()->for($otherProject)->create();
        $otherRequirement = Requirement::factory()->for($otherFeature)->create();

        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $otherFeature->id,
            'commentable_type' => 'feature',
            'message' => 'Wrong project feature',
            'project_id' => $fixture['project']->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('commentable_id');

        $this->postJson('/api/comments/add', [
            'commentable_id' => $otherRequirement->id,
            'commentable_type' => 'requirement',
            'message' => 'Wrong project requirement',
            'project_id' => $fixture['project']->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('commentable_id');
    }

    public function test_comments_reject_commentable_ids_without_a_commentable_type(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $fixture['feature']->id,
            'message' => 'Missing type',
            'project_id' => $fixture['project']->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('commentable_id');
    }

    public function test_requirement_actor_ids_must_belong_to_the_same_project_feature_relation(): void
    {
        $fixture = $this->createProjectFixture(Role::EDITOR);
        $foreignActor = Actor::factory()->for(Project::factory())->create();

        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/requirements/add', [
            'actor_ids' => [$foreignActor->id],
            'feature_id' => $fixture['feature']->id,
            'name' => 'cross project actor',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('actor_ids.0');
    }
}
