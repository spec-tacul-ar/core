<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Scopes\WithoutHistoryScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use LogicException;
use Tests\TestCase;

class HistoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_projects_create_revisions_on_update(): void
    {
        $project = Project::factory()->create(['name' => 'Old Name']);

        $project->name = 'New Name';
        $project->save();

        $this->assertCount(1, $project->history);
        $this->assertEquals(['name' => 'Old Name'], $project->history->last()['data']);
    }

    public function test_revisions_store_the_authenticated_account_sqid_and_ai_flag(): void
    {
        $account = Account::factory()->create();
        $project = Project::factory()->create(['name' => 'Old Name']);

        $this->actingAs($account);

        $project->update(['name' => 'New Name']);

        $revision = $project->history->last();

        $this->assertSame($account->sqid, $revision['account_id']);
        $this->assertFalse($revision['is_ai']);
    }

    public function test_revisions_without_an_authenticated_user_store_null_account_id(): void
    {
        $project = Project::factory()->create(['name' => 'Old Name']);

        $project->update(['name' => 'New Name']);

        $revision = $project->history->last();

        $this->assertNull($revision['account_id']);
        $this->assertFalse($revision['is_ai']);
    }

    public function test_history_is_removed_from_queries(): void
    {
        $project = Project::factory()->create(['name' => 'Old Name']);

        $project->update(['name' => 'New Name']);

        $this->assertNull(Project::find($project->id)->getRawOriginal('history'));

        $this->assertNotNull(Project::withHistory()->find($project->id)->getRawOriginal('history'));
    }

    public function test_history_scope_can_be_disabled_globally(): void
    {
        $project = Project::factory()->create(['name' => 'Old Name']);

        $project->update(['name' => 'New Name']);

        $withoutHistory = app(WithoutHistoryScope::class);

        $withoutHistory->disable();

        try {
            $this->assertNotNull(Project::find($project->id)->getRawOriginal('history'));
        } finally {
            $withoutHistory->enable();
        }

        $this->assertNull(Project::find($project->id)->getRawOriginal('history'));
    }

    public function test_projects_loaded_without_history_preserve_existing_revisions_on_update(): void
    {
        $project = Project::factory()->create(['name' => 'First Name']);

        $project->update(['name' => 'Second Name']);

        Project::find($project->id)->update(['name' => 'Third Name']);

        $project = Project::withHistory()->find($project->id);

        $this->assertCount(2, $project->history);
        $this->assertEquals(['name' => 'First Name'], $project->history->first()['data']);
        $this->assertEquals(['name' => 'Second Name'], $project->history->last()['data']);
    }

    public function test_features_create_revisions_on_delete(): void
    {
        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $feature->delete();

        $this->assertEquals(['deleted_at' => null], $feature->history->last()['data']);
    }

    public function test_projects_roll_back(): void
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create(['name' => 'Old Name']);

        $this->travelBack();

        $project->name = 'New Name';
        $project->save();

        $project->rollBack(now()->subSecond());

        $this->assertEquals('Old Name', $project->name);
    }

    public function test_projects_cannot_roll_back_before_they_were_made(): void
    {
        $this->expectException(LogicException::class);

        $this->travel(-1)->seconds();

        $project = Project::factory()->create(['name' => 'Old Name']);

        $this->travelBack();

        $project->rollBack(now()->subSeconds(2));
    }

    public function test_features_can_be_rolled_back(): void
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create(['name' => 'Old Name']);

        $this->travelBack();

        $feature->name = 'New Name';
        $feature->save();

        $project->rollBack(now()->subSecond(), ['features']);

        $this->assertEquals('Old Name', $project->features->first()->name);
    }

    public function test_features_can_be_rolled_back_after_soft_deleting()
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $this->travelBack();

        $feature->delete();

        $project->rollBack(now()->subSecond(), ['features']);

        $this->assertFalse($project->features->first()->trashed());
    }

    public function test_deleted_features_are_not_revived_after_roll_back(): void
    {
        $this->travel(-2)->seconds();

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $feature->delete();

        $this->travelBack();

        $project->rollBack(now()->subSecond(), ['features']);

        $this->assertTrue($project->features->isEmpty());
    }

    public function test_features_made_after_the_timestamp_are_not_included(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();

        $this->travelBack();

        $feature = Feature::factory()->for($project)->create();

        $project->rollBack(now()->subSecond(1), ['features']);

        $this->assertTrue($project->features->isEmpty());
    }

    public function test_relations_added_after_the_timestamp_are_not_included_after_roll_back(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();

        $this->travelBack();

        Requirement::factory()->for($feature)->create();

        $project->rollBack(now()->subSecond(1), ['features.requirements']);

        $this->assertTrue($project->features->first()->requirements->isEmpty());
    }

    public function test_has_many_through_rolls_back(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create();

        $this->travelBack();

        $feature->delete();

        $project->rollBack(now()->subSecond(1), ['requirements']);

        $this->assertTrue($project->requirements->isNotEmpty());
    }

    public function test_eager_loading_rolls_back(): void
    {
        $this->travelTo(now()->subSeconds(2));

        $project = Project::factory()->create();
        $feature = Feature::factory()->for($project)->create();
        $requirement = Requirement::factory()->for($feature)->create(['name' => 'Old Name']);

        $this->travelBack();

        $requirement->name = 'New Name';
        $requirement->save();

        $project->rollBack(now()->subSecond(), ['features.requirements']);

        $this->assertEquals('Old Name', $project->features->first()->requirements->first()->name);
    }
}
