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
use Illuminate\Validation\ValidationException;
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
            'requirement_id' => $fixture['requirement']->sqid,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estimate');

        $this->postJson('/api/requirements/add', [
            'feature_id' => $fixture['feature']->sqid,
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
            'project_id' => $project->sqid,
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

    public function test_invitations_reject_email_addresses_without_a_domain_suffix(): void
    {
        $fixture = $this->createProjectFixture(Role::OWNER);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/invitations/add', [
            'project_id' => $fixture['project']->sqid,
            'email' => 'invitee@spectacular',
            'role' => Role::VIEWER->value,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_comments_can_target_requirements_in_the_same_project(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $fixture['requirement']->sqid,
            'commentable_type' => 'requirement',
            'message' => 'Requirement comment',
            'project_id' => $fixture['project']->sqid,
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
            'commentable_id' => $otherFeature->sqid,
            'commentable_type' => 'feature',
            'message' => 'Wrong project feature',
            'project_id' => $fixture['project']->sqid,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('commentable_id');

        $this->postJson('/api/comments/add', [
            'commentable_id' => $otherRequirement->sqid,
            'commentable_type' => 'requirement',
            'message' => 'Wrong project requirement',
            'project_id' => $fixture['project']->sqid,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('commentable_id');
    }

    public function test_comments_reject_commentable_ids_without_a_commentable_type(): void
    {
        $fixture = $this->createProjectFixture(Role::VIEWER);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $fixture['feature']->sqid,
            'message' => 'Missing type',
            'project_id' => $fixture['project']->sqid,
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
            'actor_ids' => [$foreignActor->sqid],
            'feature_id' => $fixture['feature']->sqid,
            'name' => 'cross project actor',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('actor_ids.0');
    }

    public function test_malformed_sqids_are_reported_as_validation_errors(): void
    {
        $fixture = $this->createProjectFixture(Role::EDITOR);
        $this->actingAsAccount($fixture['account']);

        $this->postJson('/api/features/add', [
            'name' => 'Malformed feature',
            'project_id' => 'not-a-sqid',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('project_id');

        $this->postJson('/api/requirements/add', [
            'actor_ids' => ['not-a-sqid'],
            'feature_id' => $fixture['feature']->sqid,
            'name' => 'Malformed actor assignment',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('actor_ids.0');
    }

    public function test_project_import_requires_requirement_collections_to_be_arrays(): void
    {
        try {
            Project::import([
                'name' => 'Imported Project',
                'description' => null,
                'actors' => [
                    [
                        'id' => 1,
                        'name' => 'Operators',
                        'summary' => null,
                        'weight' => null,
                    ],
                ],
                'features' => [
                    [
                        'name' => 'Feature',
                        'description' => null,
                        'weight' => null,
                        'requirements' => [
                            [
                                'name' => 'Requirement',
                                'description' => null,
                                'blocked_reason' => null,
                                'source' => null,
                                'reference' => null,
                                'weight' => null,
                                'tasks' => null,
                                'unknowns' => null,
                                'actor_ids' => null,
                            ],
                        ],
                    ],
                ],
            ]);

            $this->fail('Expected import validation to reject null requirement collections.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('features.0.requirements.0.tasks', $exception->errors());
            $this->assertArrayHasKey('features.0.requirements.0.unknowns', $exception->errors());
            $this->assertArrayHasKey('features.0.requirements.0.actor_ids', $exception->errors());
        }
    }
}
