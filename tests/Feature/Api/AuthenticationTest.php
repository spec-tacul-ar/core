<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    #[DataProvider('protectedRouteProvider')]
    public function test_custom_api_routes_require_authentication(string $method, string $uri, array $payload = []): void
    {
        $fixture = $this->createProjectFixture();

        $comment = Comment::factory()
            ->for($fixture['account'], 'account')
            ->for($fixture['project'])
            ->create([
                'commentable_id' => $fixture['feature']->id,
                'commentable_type' => 'feature',
            ]);

        $invitation = Invitation::factory()
            ->for($fixture['account'], 'account')
            ->for($fixture['project'])
            ->create([
                'email' => 'invitee@example.test',
                'role' => Role::VIEWER,
            ]);

        $bindings = [
            'comment' => $comment->id,
            'contributor' => $fixture['contributor']->id,
            'feature' => $fixture['feature']->id,
            'invitation' => $invitation->id,
            'project' => $fixture['project']->id,
            'requirement' => $fixture['requirement']->id,
            'task' => $fixture['task']->id,
            'unknown' => $fixture['unknown']->id,
            'user' => $fixture['projectUser']->id,
        ];

        $response = $this->json(
            $method,
            $this->interpolatePlaceholders($uri, $bindings),
            $this->interpolatePlaceholders($payload, $bindings),
        );

        $response->assertUnauthorized();
    }

    public function test_auth_account_endpoint_returns_the_authenticated_account(): void
    {
        $account = $this->actingAsAccount();

        $response = $this->getJson('/api/auth/account');

        $response->assertOk();
        $response->assertJsonPath('data.id', $account->id);
        $response->assertJsonPath('data.name', $account->name);
        $response->assertJsonPath('data.email', $account->email);
        $response->assertJsonMissingPath('data.is_solo');
    }

    public function test_auth_account_endpoint_does_not_activate_solo_mode_when_accounts_exist(): void
    {
        Account::factory()->create();

        $this->getJson('/api/auth/account')->assertUnauthorized();
    }

    public function test_auth_account_endpoint_returns_the_solo_account_when_there_are_no_accounts(): void
    {
        $response = $this->getJson('/api/auth/account');

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Default');
        $response->assertJsonPath('data.email', 'solo@spectacular');
        $response->assertJsonPath('data.is_solo', true);
    }

    public static function protectedRouteProvider(): array
    {
        return [
            'account.read' => ['GET', '/api/auth/account'],
            'account.edit' => ['POST', '/api/account/edit', ['name' => 'Renamed']],
            'account.delete' => ['POST', '/api/account/delete', ['confirmation' => true]],
            'comments.add' => ['POST', '/api/comments/add', [
                'commentable_id' => '{feature}',
                'commentable_type' => 'feature',
                'message' => 'A comment',
                'project_id' => '{project}',
            ]],
            'comments.browse' => ['GET', '/api/comments/browse?project_id={project}'],
            'comments.delete' => ['POST', '/api/comments/{comment}/delete'],
            'contributors.edit' => ['POST', '/api/contributors/{contributor}/edit', ['role' => Role::EDITOR->value]],
            'contributors.delete' => ['POST', '/api/contributors/{contributor}/delete'],
            'features.add' => ['POST', '/api/features/add', [
                'name' => 'Workflow',
                'project_id' => '{project}',
            ]],
            'features.edit' => ['POST', '/api/features/{feature}/edit', ['name' => 'Workflow Updated']],
            'features.delete' => ['POST', '/api/features/{feature}/delete'],
            'invitations.add' => ['POST', '/api/invitations/add', [
                'email' => 'invitee@example.test',
                'project_id' => '{project}',
                'role' => Role::VIEWER->value,
            ]],
            'invitations.browse' => ['GET', '/api/invitations/browse?project_id={project}'],
            'invitations.accept' => ['POST', '/api/invitations/{invitation}/accept'],
            'invitations.delete' => ['POST', '/api/invitations/{invitation}/delete'],
            'projects.add' => ['POST', '/api/projects/add', ['name' => 'Roadmap']],
            'projects.browse' => ['GET', '/api/projects/browse'],
            'projects.delete' => ['POST', '/api/projects/{project}/delete'],
            'projects.edit' => ['POST', '/api/projects/{project}/edit', ['name' => 'Roadmap Updated']],
            'projects.organise' => ['POST', '/api/projects/{project}/organise', [
                'features' => [],
                'requirements' => [],
                'users' => [],
            ]],
            'projects.read' => ['GET', '/api/projects/{project}/read'],
            'projects.readmark' => ['POST', '/api/projects/{project}/readmark'],
            'requirements.add' => ['POST', '/api/requirements/add', [
                'feature_id' => '{feature}',
                'name' => 'deliver notifications',
                'tasks' => [],
                'unknowns' => [],
                'user_ids' => [],
            ]],
            'requirements.edit' => ['POST', '/api/requirements/{requirement}/edit', ['name' => 'deliver updated notifications']],
            'requirements.delete' => ['POST', '/api/requirements/{requirement}/delete'],
            'requirements.complete' => ['POST', '/api/requirements/{requirement}/tasks/complete'],
            'tasks.add' => ['POST', '/api/tasks/add', [
                'is_complete' => false,
                'name' => 'Ship work',
                'requirement_id' => '{requirement}',
            ]],
            'tasks.edit' => ['POST', '/api/tasks/{task}/edit', ['name' => 'Ship updated work']],
            'tasks.delete' => ['POST', '/api/tasks/{task}/delete'],
            'unknowns.add' => ['POST', '/api/unknowns/add', [
                'name' => 'Who approves?',
                'requirement_id' => '{requirement}',
            ]],
            'unknowns.edit' => ['POST', '/api/unknowns/{unknown}/edit', ['name' => 'Who approves now?']],
            'unknowns.delete' => ['POST', '/api/unknowns/{unknown}/delete'],
            'users.add' => ['POST', '/api/users/add', [
                'name' => 'Operators',
                'project_id' => '{project}',
            ]],
            'users.edit' => ['POST', '/api/users/{user}/edit', ['name' => 'Operators Updated']],
            'users.delete' => ['POST', '/api/users/{user}/delete'],
        ];
    }
}
