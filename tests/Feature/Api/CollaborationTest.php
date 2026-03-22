<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Invitation;
use App\Models\Project;
use App\Notifications\InvitationAccepted;
use App\Notifications\InvitationForNewAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class CollaborationTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_comments_add_endpoint_allows_editors_and_rejects_viewers(): void
    {
        $project = Project::factory()->create();
        $feature = \App\Models\Feature::factory()->for($project)->create();

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $feature->id,
            'commentable_type' => 'feature',
            'message' => 'Editor comment',
            'project_id' => $project->id,
        ])->assertCreated();

        $this->assertDatabaseHas('comments', [
            'account_id' => $editor->id,
            'commentable_id' => $feature->id,
            'commentable_type' => 'feature',
            'project_id' => $project->id,
        ]);

        $this->actingAsAccount($viewer);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $feature->id,
            'commentable_type' => 'feature',
            'message' => 'Viewer comment',
            'project_id' => $project->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('project_id');
    }

    public function test_comments_browse_endpoint_allows_viewers_but_not_outsiders(): void
    {
        $project = Project::factory()->create();
        $feature = \App\Models\Feature::factory()->for($project)->create();

        $owner = Account::factory()->create();
        $viewer = Account::factory()->create();
        $outsider = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        Comment::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'commentable_id' => $feature->id,
                'commentable_type' => 'feature',
            ]);

        $this->actingAsAccount($viewer);

        $this->getJson('/api/comments/browse?project_id=' . $project->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAsAccount($outsider);

        $this->getJson('/api/comments/browse?project_id=' . $project->id)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('project_id');
    }

    public function test_comments_delete_endpoint_allows_comment_authors_and_project_owners_only(): void
    {
        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $author = Account::factory()->create();
        $otherEditor = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($author, $project, Role::EDITOR);
        $this->attachContributor($otherEditor, $project, Role::EDITOR);

        $comment = Comment::factory()
            ->for($author, 'account')
            ->for($project)
            ->create();

        $this->actingAsAccount($otherEditor);
        $this->postJson('/api/comments/' . $comment->id . '/delete')->assertForbidden();

        $authorComment = Comment::factory()
            ->for($author, 'account')
            ->for($project)
            ->create();

        $this->actingAsAccount($author);
        $this->postJson('/api/comments/' . $authorComment->id . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $authorComment->id]);

        $this->actingAsAccount($owner);
        $this->postJson('/api/comments/' . $comment->id . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_invitations_add_endpoint_allows_editors_and_rejects_viewers(): void
    {
        Notification::fake();

        $project = Project::factory()->create();

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);

        $this->postJson('/api/invitations/add', [
            'email' => 'new-person@example.test',
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ])->assertCreated();

        $invitation = Invitation::query()->where('email', 'new-person@example.test')->firstOrFail();

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ]);
        Notification::assertSentOnDemand(InvitationForNewAccount::class);

        $this->actingAsAccount($viewer);

        $this->postJson('/api/invitations/add', [
            'email' => 'blocked@example.test',
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ])->assertUnprocessable()->assertJsonValidationErrors('project_id');
    }

    public function test_invitations_browse_endpoint_returns_project_and_incoming_invitations_without_leaking_other_projects(): void
    {
        $project = Project::factory()->create();
        $otherProject = Project::factory()->create();

        $owner = Account::factory()->create();
        $recipient = Account::factory()->create(['email' => 'invitee@example.test']);
        $outsider = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);

        $projectInvitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => $recipient->email,
                'role' => Role::VIEWER,
            ]);

        Invitation::factory()
            ->for($owner, 'account')
            ->for($otherProject)
            ->create([
                'email' => 'someone-else@example.test',
                'role' => Role::EDITOR,
            ]);

        $this->actingAsAccount($owner);

        $this->getJson('/api/invitations/browse?project_id=' . $project->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $projectInvitation->id);

        $this->actingAsAccount($recipient);

        $this->getJson('/api/invitations/browse')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $projectInvitation->id);

        $this->actingAsAccount($outsider);

        $this->getJson('/api/invitations/browse?project_id=' . $project->id)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('project_id');
    }

    public function test_invitations_accept_endpoint_is_limited_to_the_invited_account(): void
    {
        Notification::fake();

        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $recipient = Account::factory()->create(['email' => 'invitee@example.test']);
        $outsider = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);

        $invitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => $recipient->email,
                'role' => Role::VIEWER,
            ]);

        $this->actingAsAccount($outsider);
        $this->postJson('/api/invitations/' . $invitation->id . '/accept')->assertForbidden();

        $this->actingAsAccount($recipient);
        $this->postJson('/api/invitations/' . $invitation->id . '/accept')->assertNoContent();

        $this->assertDatabaseHas('contributors', [
            'account_id' => $recipient->id,
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ]);
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
        Notification::assertSentTo($owner, InvitationAccepted::class);
    }

    public function test_invitations_delete_endpoint_allows_project_owners_and_recipients_only(): void
    {
        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $recipient = Account::factory()->create(['email' => 'recipient@example.test']);
        $outsider = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);

        $recipientInvitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => $recipient->email,
            ]);

        $ownerInvitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => 'other@example.test',
            ]);

        $this->actingAsAccount($outsider);
        $this->postJson('/api/invitations/' . $recipientInvitation->id . '/delete')->assertForbidden();

        $this->actingAsAccount($recipient);
        $this->postJson('/api/invitations/' . $recipientInvitation->id . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('invitations', ['id' => $recipientInvitation->id]);

        $this->actingAsAccount($owner);
        $this->postJson('/api/invitations/' . $ownerInvitation->id . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('invitations', ['id' => $ownerInvitation->id]);
    }

    public function test_contributors_edit_endpoint_requires_owner_permissions(): void
    {
        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($editor, $project, Role::EDITOR);
        $viewerContributor = $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);
        $this->postJson('/api/contributors/' . $viewerContributor->id . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertForbidden();

        $this->actingAsAccount($owner);
        $this->postJson('/api/contributors/' . $viewerContributor->id . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertOk();

        $this->assertDatabaseHas('contributors', [
            'id' => $viewerContributor->id,
            'role' => Role::EDITOR->value,
        ]);
    }

    public function test_contributors_delete_endpoint_cannot_remove_the_last_owner_and_allows_other_removals(): void
    {
        $soleOwnerProject = Project::factory()->create();
        $soleOwner = Account::factory()->create();
        $soleOwnerContributor = $this->attachContributor($soleOwner, $soleOwnerProject, Role::OWNER);

        $this->actingAsAccount($soleOwner);
        $this->postJson('/api/contributors/' . $soleOwnerContributor->id . '/delete')->assertForbidden();

        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $viewerContributor = $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($owner);
        $this->postJson('/api/contributors/' . $viewerContributor->id . '/delete')->assertNoContent();

        $this->assertDatabaseMissing('contributors', ['id' => $viewerContributor->id]);
    }
}
