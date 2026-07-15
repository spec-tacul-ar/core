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
            'comment' => $comment->sqid,
            'collaboration' => $fixture['collaboration']->sqid,
            'feature' => $fixture['feature']->sqid,
            'invitation' => $invitation->sqid,
            'project' => $fixture['project']->sqid,
            'requirement' => $fixture['requirement']->sqid,
            'task' => $fixture['task']->sqid,
            'unknown' => $fixture['unknown']->sqid,
            'actor' => $fixture['projectActor']->sqid,
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

        $response = $this->getJson('/api/account');

        $response->assertOk();
        $response->assertJsonPath('data.id', $account->sqid);
        $response->assertJsonPath('data.name', $account->name);
        $response->assertJsonPath('data.email', $account->email);
        $response->assertJsonMissingPath('data.is_email_verified');
    }

    public function test_auth_account_endpoint_rejects_unverified_accounts(): void
    {
        $this->actingAsAccount(Account::factory()->unverified()->create());

        $this->getJson('/api/account')
            ->assertForbidden();
    }

    public static function protectedRouteProvider(): array
    {
        return [
            'account.read' => ['GET', '/api/account'],
            'account.edit' => ['POST', '/api/account/edit', ['name' => 'Renamed']],
            'account.delete' => ['POST', '/api/account/delete', ['confirmation' => true]],
            'comments.add' => ['POST', '/api/comments', [
                'commentable_id' => '{feature}',
                'commentable_type' => 'feature',
                'message' => 'A comment',
                'project_id' => '{project}',
            ]],
            'comments.browse' => ['GET', '/api/comments?project_id={project}'],
            'collaborations.browse' => ['GET', '/api/collaborations?project_id={project}'],
            'comments.delete' => ['POST', '/api/comments/{comment}/delete'],
            'collaborations.edit' => ['POST', '/api/collaborations/{collaboration}/edit', ['role' => Role::EDITOR->value]],
            'collaborations.delete' => ['POST', '/api/collaborations/{collaboration}/delete'],
            'features.add' => ['POST', '/api/features', [
                'name' => 'Workflow',
                'project_id' => '{project}',
            ]],
            'features.edit' => ['POST', '/api/features/{feature}/edit', ['name' => 'Workflow Updated']],
            'features.delete' => ['POST', '/api/features/{feature}/delete'],
            'invitations.add' => ['POST', '/api/invitations', [
                'email' => 'invitee@example.test',
                'project_id' => '{project}',
                'role' => Role::VIEWER->value,
            ]],
            'invitations.browse' => ['GET', '/api/invitations?project_id={project}'],
            'invitations.accept' => ['POST', '/api/invitations/{invitation}/accept'],
            'invitations.delete' => ['POST', '/api/invitations/{invitation}/delete'],
            'projects.add' => ['POST', '/api/projects', ['name' => 'Roadmap']],
            'projects.browse' => ['GET', '/api/projects'],
            'projects.delete' => ['POST', '/api/projects/{project}/delete'],
            'projects.edit' => ['POST', '/api/projects/{project}/edit', ['name' => 'Roadmap Updated']],
            'projects.organise' => ['POST', '/api/projects/{project}/organise', [
                'features' => [],
                'requirements' => [],
                'actors' => [],
            ]],
            'projects.read' => ['GET', '/api/projects/{project}'],
            'projects.readmark' => ['POST', '/api/projects/{project}/readmark'],
            'requirements.add' => ['POST', '/api/requirements', [
                'feature_id' => '{feature}',
                'name' => 'deliver notifications',
                'tasks' => [],
                'unknowns' => [],
                'actor_ids' => [],
            ]],
            'requirements.append' => ['POST', '/api/requirements/{requirement}/append', ['text' => 'Answered by the client.']],
            'requirements.block' => ['POST', '/api/requirements/{requirement}/block', ['reason' => 'Waiting on the client.']],
            'requirements.edit' => ['POST', '/api/requirements/{requirement}/edit', ['name' => 'deliver updated notifications']],
            'requirements.delete' => ['POST', '/api/requirements/{requirement}/delete'],
            'requirements.complete' => ['POST', '/api/requirements/{requirement}/complete'],
            'requirements.unblock' => ['POST', '/api/requirements/{requirement}/unblock'],
            'tasks.edit' => ['POST', '/api/tasks/{task}/edit', ['name' => 'Ship updated work']],
            'tasks.toggle' => ['POST', '/api/tasks/{task}/toggle', ['is_complete' => true]],
            'tasks.delete' => ['POST', '/api/tasks/{task}/delete'],
            'unknowns.edit' => ['POST', '/api/unknowns/{unknown}/edit', ['name' => 'Who approves now?']],
            'unknowns.delete' => ['POST', '/api/unknowns/{unknown}/delete'],
            'actors.add' => ['POST', '/api/actors', [
                'name' => 'Operators',
                'project_id' => '{project}',
            ]],
            'actors.read' => ['GET', '/api/actors/{actor}'],
            'actors.edit' => ['POST', '/api/actors/{actor}/edit', ['name' => 'Operators Updated']],
            'actors.delete' => ['POST', '/api/actors/{actor}/delete'],
        ];
    }
}
