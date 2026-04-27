<?php

namespace Tests\Feature\Api;

use App\Enums\Role;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Invitation;
use App\Models\Project;
use App\Notifications\InvitationAccepted;
use App\Notifications\InvitationForExistingAccount;
use App\Notifications\InvitationForNewAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class CollaborationTest extends TestCase
{
    use BuildsApiFixtures;
    use RefreshDatabase;

    public function test_comments_add_endpoint_allows_editors_and_viewers(): void
    {
        $project = Project::factory()->create();
        $feature = \App\Models\Feature::factory()->for($project)->create();

        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($editor);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $feature->sqid,
            'commentable_type' => 'feature',
            'message' => 'Editor comment',
            'project_id' => $project->sqid,
        ])->assertCreated();

        $this->assertDatabaseHas('comments', [
            'account_id' => $editor->id,
            'commentable_id' => $feature->id,
            'commentable_type' => 'feature',
            'project_id' => $project->id,
        ]);

        $this->actingAsAccount($viewer);

        $this->postJson('/api/comments/add', [
            'commentable_id' => $feature->sqid,
            'commentable_type' => 'feature',
            'message' => 'Viewer comment',
            'project_id' => $project->sqid,
        ])->assertCreated();

        $this->assertDatabaseHas('comments', [
            'account_id' => $viewer->id,
            'commentable_id' => $feature->id,
            'commentable_type' => 'feature',
            'project_id' => $project->id,
        ]);
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

        $this->getJson('/api/comments/browse?project_id=' . $project->sqid)
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAsAccount($outsider);

        $this->getJson('/api/comments/browse?project_id=' . $project->sqid)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('project_id');
    }

    public function test_comments_browse_endpoint_orders_newest_first_and_serializes_commentable_names(): void
    {
        $project = Project::factory()->create();
        $feature = \App\Models\Feature::factory()->for($project)->create(['name' => 'billing portal']);
        $requirement = \App\Models\Requirement::factory()->for($feature)->create(['name' => 'download invoices']);
        $otherProject = Project::factory()->create();
        $otherFeature = \App\Models\Feature::factory()->for($otherProject)->create();

        $account = Account::factory()->create();
        $this->attachContributor($account, $project, Role::VIEWER);

        $older = Comment::factory()
            ->for($account, 'account')
            ->for($project)
            ->create([
                'commentable_id' => $feature->id,
                'commentable_type' => 'feature',
                'created_at' => now()->subMinute(),
                'message' => 'Older feature comment',
            ]);

        $newer = Comment::factory()
            ->for($account, 'account')
            ->for($project)
            ->create([
                'commentable_id' => $requirement->id,
                'commentable_type' => 'requirement',
                'created_at' => now(),
                'message' => 'Newer requirement comment',
            ]);

        Comment::factory()
            ->for($account, 'account')
            ->for($otherProject)
            ->create([
                'commentable_id' => $otherFeature->id,
                'commentable_type' => 'feature',
                'message' => 'Other project comment',
            ]);

        $this->actingAsAccount($account);

        $this->getJson('/api/comments/browse?project_id=' . $project->sqid)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->sqid)
            ->assertJsonPath('data.0.commentable_name', 'Download invoices')
            ->assertJsonPath('data.1.id', $older->sqid)
            ->assertJsonPath('data.1.commentable_name', 'Billing portal')
            ->assertJsonMissing(['message' => 'Other project comment']);
    }

    public function test_comments_delete_endpoint_allows_comment_authors_only(): void
    {
        $project = Project::factory()->create();

        $author = Account::factory()->create();
        $otherEditor = Account::factory()->create();

        $this->attachContributor($author, $project, Role::EDITOR);
        $this->attachContributor($otherEditor, $project, Role::EDITOR);

        $comment = Comment::factory()
            ->for($author, 'account')
            ->for($project)
            ->create();

        $this->actingAsAccount($otherEditor);
        $this->postJson('/api/comments/' . $comment->sqid . '/delete')->assertForbidden();

        $authorComment = Comment::factory()
            ->for($author, 'account')
            ->for($project)
            ->create();

        $this->actingAsAccount($author);
        $this->postJson('/api/comments/' . $authorComment->sqid . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $authorComment->id]);

        $owner = Account::factory()->create();
        $this->attachContributor($owner, $project, Role::OWNER);

        $ownerBlockedComment = Comment::factory()
            ->for($author, 'account')
            ->for($project)
            ->create();

        $this->actingAsAccount($owner);
        $this->postJson('/api/comments/' . $ownerBlockedComment->sqid . '/delete')->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $ownerBlockedComment->id]);
    }

    public function test_invitations_add_endpoint_allows_owners_and_rejects_non_owners(): void
    {
        Notification::fake();

        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $editor = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $this->attachContributor($editor, $project, Role::EDITOR);
        $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($owner);

        $this->postJson('/api/invitations/add', [
            'email' => 'new-person@example.test',
            'project_id' => $project->sqid,
            'role' => Role::VIEWER->value,
        ])->assertCreated();

        $invitation = Invitation::query()->where('email', 'new-person@example.test')->firstOrFail();

        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ]);
        Notification::assertSentOnDemand(InvitationForNewAccount::class);

        $this->actingAsAccount($editor);

        $this->postJson('/api/invitations/add', [
            'email' => 'blocked-editor@example.test',
            'project_id' => $project->sqid,
            'role' => Role::VIEWER->value,
        ])->assertUnprocessable()->assertJsonValidationErrors('project_id');

        $this->actingAsAccount($viewer);

        $this->postJson('/api/invitations/add', [
            'email' => 'blocked-viewer@example.test',
            'project_id' => $project->sqid,
            'role' => Role::VIEWER->value,
        ])->assertUnprocessable()->assertJsonValidationErrors('project_id');
    }

    public function test_invitations_add_endpoint_notifies_existing_accounts_directly(): void
    {
        Notification::fake();

        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $recipient = Account::factory()->create(['email' => 'existing@example.test']);

        $this->attachContributor($owner, $project, Role::OWNER);

        $this->actingAsAccount($owner);

        $this->postJson('/api/invitations/add', [
            'email' => $recipient->email,
            'project_id' => $project->sqid,
            'role' => Role::EDITOR->value,
        ])->assertCreated();

        $invitation = Invitation::query()->where('email', $recipient->email)->firstOrFail();

        Notification::assertSentTo(
            $recipient,
            InvitationForExistingAccount::class,
            fn(InvitationForExistingAccount $notification) => $notification->invitation->is($invitation),
        );
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

        $this->getJson('/api/invitations/browse?project_id=' . $project->sqid)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $projectInvitation->sqid)
            ->assertJsonPath('data.0.account_name', $owner->name)
            ->assertJsonMissingPath('data.0.project_name');

        $this->actingAsAccount($recipient);

        $this->getJson('/api/invitations/browse')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $projectInvitation->sqid)
            ->assertJsonPath('data.0.project_name', $project->name)
            ->assertJsonMissingPath('data.0.account_name');

        $this->actingAsAccount($outsider);

        $this->getJson('/api/invitations/browse?project_id=' . $project->sqid)
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
        $this->postJson('/api/invitations/' . $invitation->sqid . '/accept')->assertNotFound();

        $this->actingAsAccount($recipient);
        $this->postJson('/api/invitations/' . $invitation->sqid . '/accept')->assertNoContent();

        $this->assertDatabaseHas('contributors', [
            'account_id' => $recipient->id,
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ]);
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
        Notification::assertSentTo($owner, InvitationAccepted::class);
    }

    public function test_invitations_accept_endpoint_requires_a_verified_email_address(): void
    {
        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $recipient = Account::factory()->unverified()->create(['email' => 'invitee@example.test']);

        $this->attachContributor($owner, $project, Role::OWNER);

        $invitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => $recipient->email,
                'role' => Role::VIEWER,
            ]);

        $this->actingAsAccount($recipient);
        $this->postJson('/api/invitations/' . $invitation->sqid . '/accept')
            ->assertForbidden();

        $this->assertDatabaseMissing('contributors', [
            'account_id' => $recipient->id,
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
    }

    public function test_signed_invitation_route_redirects_guests_to_the_app(): void
    {
        $project = Project::factory()->create();
        $owner = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);

        $invitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => 'invitee@example.test',
                'role' => Role::VIEWER,
            ]);

        $url = URL::signedRoute('invitations.accept', $invitation);

        $this->get($url)
            ->assertRedirect(config('spectacular.path'));
    }

    public function test_signed_invitation_route_verifies_the_email_and_accepts_the_invitation(): void
    {
        Notification::fake();

        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $recipient = Account::factory()->unverified()->create(['email' => 'invitee@example.test']);

        $this->attachContributor($owner, $project, Role::OWNER);

        $invitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => $recipient->email,
                'role' => Role::VIEWER,
            ]);

        $this->actingAs($recipient);

        $this->get(URL::signedRoute('invitations.accept', $invitation))
            ->assertRedirect('/projects/' . $project->sqid);

        $this->assertTrue($recipient->fresh()->hasVerifiedEmail());
        $this->assertDatabaseHas('contributors', [
            'account_id' => $recipient->id,
            'project_id' => $project->id,
            'role' => Role::VIEWER->value,
        ]);
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
        Notification::assertSentTo($owner, InvitationAccepted::class);
    }

    public function test_signed_invitation_route_is_forbidden_for_a_different_authenticated_account(): void
    {
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

        $this->actingAs($outsider);

        $this->get(URL::signedRoute('invitations.accept', $invitation))
            ->assertNotFound();

        $this->assertDatabaseMissing('contributors', [
            'account_id' => $outsider->id,
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
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
        $this->postJson('/api/invitations/' . $recipientInvitation->sqid . '/delete')->assertNotFound();

        $this->actingAsAccount($recipient);
        $this->postJson('/api/invitations/' . $recipientInvitation->sqid . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('invitations', ['id' => $recipientInvitation->id]);

        $this->actingAsAccount($owner);
        $this->postJson('/api/invitations/' . $ownerInvitation->sqid . '/delete')->assertNoContent();
        $this->assertDatabaseMissing('invitations', ['id' => $ownerInvitation->id]);
    }

    public function test_invitations_delete_endpoint_requires_a_verified_email_address_for_recipients(): void
    {
        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $recipient = Account::factory()->unverified()->create(['email' => 'recipient@example.test']);

        $this->attachContributor($owner, $project, Role::OWNER);

        $invitation = Invitation::factory()
            ->for($owner, 'account')
            ->for($project)
            ->create([
                'email' => $recipient->email,
            ]);

        $this->actingAsAccount($recipient);
        $this->postJson('/api/invitations/' . $invitation->sqid . '/delete')
            ->assertForbidden();

        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
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
        $this->postJson('/api/contributors/' . $viewerContributor->sqid . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertForbidden();

        $this->actingAsAccount($owner);
        $this->postJson('/api/contributors/' . $viewerContributor->sqid . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertOk();

        $this->assertDatabaseHas('contributors', [
            'id' => $viewerContributor->id,
            'role' => Role::EDITOR->value,
        ]);
    }

    public function test_newer_owners_cannot_demote_older_owners(): void
    {
        $project = Project::factory()->create();

        $olderOwner = Account::factory()->create();
        $newerOwner = Account::factory()->create();

        $olderContributor = $this->attachContributor($olderOwner, $project, Role::OWNER);
        $newerContributor = $this->attachContributor($newerOwner, $project, Role::OWNER);

        $olderContributor->forceFill(['updated_at' => now()->subMinutes(10)])->saveQuietly();
        $newerContributor->forceFill(['updated_at' => now()])->saveQuietly();

        $this->actingAsAccount($newerOwner);

        $this->postJson('/api/contributors/' . $olderContributor->sqid . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertForbidden();

        $this->assertDatabaseHas('contributors', [
            'id' => $olderContributor->id,
            'role' => Role::OWNER->value,
        ]);
    }

    public function test_older_owners_can_demote_newer_owners(): void
    {
        $project = Project::factory()->create();

        $olderOwner = Account::factory()->create();
        $newerOwner = Account::factory()->create();

        $olderContributor = $this->attachContributor($olderOwner, $project, Role::OWNER);
        $newerContributor = $this->attachContributor($newerOwner, $project, Role::OWNER);

        $olderContributor->forceFill(['updated_at' => now()->subMinutes(10)])->saveQuietly();
        $newerContributor->forceFill(['updated_at' => now()])->saveQuietly();

        $this->actingAsAccount($olderOwner);

        $this->postJson('/api/contributors/' . $newerContributor->sqid . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertOk();

        $this->assertDatabaseHas('contributors', [
            'id' => $newerContributor->id,
            'role' => Role::EDITOR->value,
        ]);
    }

    public function test_owners_cannot_demote_other_owners_with_the_same_timestamp(): void
    {
        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $peer = Account::factory()->create();
        $timestamp = now()->subMinute();

        $ownerContributor = $this->attachContributor($owner, $project, Role::OWNER);
        $peerContributor = $this->attachContributor($peer, $project, Role::OWNER);

        $ownerContributor->forceFill(['updated_at' => $timestamp])->saveQuietly();
        $peerContributor->forceFill(['updated_at' => $timestamp])->saveQuietly();

        $this->actingAsAccount($owner);

        $this->postJson('/api/contributors/' . $peerContributor->sqid . '/edit', [
            'role' => Role::EDITOR->value,
        ])->assertForbidden();
    }

    public function test_contributors_delete_endpoint_cannot_remove_the_last_owner_and_allows_other_removals(): void
    {
        $soleOwnerProject = Project::factory()->create();
        $soleOwner = Account::factory()->create();
        $soleOwnerContributor = $this->attachContributor($soleOwner, $soleOwnerProject, Role::OWNER);

        $this->actingAsAccount($soleOwner);
        $this->postJson('/api/contributors/' . $soleOwnerContributor->sqid . '/delete')->assertForbidden();

        $project = Project::factory()->create();
        $owner = Account::factory()->create();
        $viewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $viewerContributor = $this->attachContributor($viewer, $project, Role::VIEWER);

        $this->actingAsAccount($owner);
        $this->postJson('/api/contributors/' . $viewerContributor->sqid . '/delete')->assertNoContent();

        $this->assertDatabaseMissing('contributors', ['id' => $viewerContributor->id]);
    }

    public function test_newer_owners_cannot_delete_older_owners_but_older_owners_can_delete_newer_owners(): void
    {
        $project = Project::factory()->create();

        $olderOwner = Account::factory()->create();
        $newerOwner = Account::factory()->create();

        $olderContributor = $this->attachContributor($olderOwner, $project, Role::OWNER);
        $newerContributor = $this->attachContributor($newerOwner, $project, Role::OWNER);

        $olderContributor->forceFill(['updated_at' => now()->subMinutes(10)])->saveQuietly();
        $newerContributor->forceFill(['updated_at' => now()])->saveQuietly();

        $this->actingAsAccount($newerOwner);

        $this->postJson('/api/contributors/' . $olderContributor->sqid . '/delete')->assertForbidden();

        $this->actingAsAccount($olderOwner);

        $this->postJson('/api/contributors/' . $newerContributor->sqid . '/delete')->assertNoContent();

        $this->assertDatabaseHas('contributors', ['id' => $olderContributor->id]);
        $this->assertDatabaseMissing('contributors', ['id' => $newerContributor->id]);
    }

    public function test_non_owner_contributors_can_remove_themselves_but_not_other_contributors(): void
    {
        $project = Project::factory()->create();

        $owner = Account::factory()->create();
        $viewer = Account::factory()->create();
        $otherViewer = Account::factory()->create();

        $this->attachContributor($owner, $project, Role::OWNER);
        $viewerContributor = $this->attachContributor($viewer, $project, Role::VIEWER);
        $otherViewerContributor = $this->attachContributor($otherViewer, $project, Role::VIEWER);

        $this->actingAsAccount($viewer);

        $this->postJson('/api/contributors/' . $otherViewerContributor->sqid . '/delete')->assertForbidden();

        $this->postJson('/api/contributors/' . $viewerContributor->sqid . '/delete')->assertNoContent();

        $this->assertDatabaseMissing('contributors', ['id' => $viewerContributor->id]);
        $this->assertDatabaseHas('contributors', ['id' => $otherViewerContributor->id]);
    }
}
