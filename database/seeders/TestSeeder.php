<?php

namespace Database\Seeders;

use Faker\Generator as Faker;
use Illuminate\Database\Seeder;
use App\Enums\Role;
use App\Models\Feature;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Account;
use App\Models\Comment;
use App\Models\Contributor;
use App\Models\Invitation;

class TestSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = resolve(Faker::class);
    }

    public function run()
    {
        $this->callSilent(DatabaseSeeder::class);

        $account = Account::factory()
            ->state(['email' => 'user@example.com'])
            ->create();

        $other_account = Account::factory()
            ->state(['email' => 'other.user@example.com'])
            ->create();

        $unverified_account = Account::factory()
            ->state(['email' => 'unverified@example.com'])
            ->unverified()
            ->create();

        $project = Project::factory()
            ->state(['name' => 'My Project'])
            ->has(Contributor::factory()->state(['role' => Role::OWNER])->for($account))
            ->has(Contributor::factory()->state(['role' => Role::EDITOR]))
            ->has(Contributor::factory()->state(['role' => Role::VIEWER]))
            ->has(Invitation::factory()->state(['role' => Role::OWNER])->for($account))
            ->has(Invitation::factory()->state(['role' => Role::EDITOR])->for($account))
            ->has(Invitation::factory()->state(['role' => Role::VIEWER])->for($account))
            ->hasActors($this->faker->numberBetween(2, 4))
            ->has(
                Feature::factory()
                    ->count($this->faker->numberBetween(2, 5))
                    ->has(
                        Requirement::factory()
                            ->count($this->faker->numberBetween(1, 5))
                            ->hasTasks($this->faker->numberBetween(1, 3))
                            ->hasUnknowns($this->faker->numberBetween(0, 3))
                            ->afterCreating(function (Requirement $requirement) {
                                $actors = $this->faker->randomElements($requirement->feature->project->actors, null);

                                foreach ($actors as $actor) {
                                    $requirement->assignments()->make()->actor()->associate($actor)->save();
                                }
                            })
                    )
            )
            ->create();

        Project::factory()
            ->state(['name' => 'Other Project'])
            ->has(Contributor::factory()->state(['role' => Role::OWNER])->for($other_account))
            ->has(Invitation::factory()->state(['email' => $account->email, 'role' => Role::OWNER])->for($other_account))
            ->has(Invitation::factory()->state(['email' => $unverified_account->email, 'role' => Role::OWNER])->for($other_account))
            ->create();

        Project::factory()
            ->state(['name' => 'Empty Project'])
            ->has(Contributor::factory()->state(['role' => Role::OWNER])->for($account))
            ->create();

        Project::factory()
            ->state(['name' => 'Two owners'])
            ->has(Contributor::factory()->state(['role' => Role::OWNER])->for($account))
            ->has(Contributor::factory()->state(['role' => Role::OWNER, 'created_at' => now()->addSecond(), 'updated_at' => now()->addSecond()]));

        // Feature comment
        Comment::factory()
            ->for($project)
            ->for($account)
            ->for($project->features->first(), 'commentable')
            ->create([
                'created_at' => now()->subMinutes(60),
            ]);

        // Requirement comment
        Comment::factory()
            ->for($project)
            ->for($account)
            ->for($project->requirements->first(), 'commentable')
            ->create([
                'created_at' => now()->subMinutes(40),
            ]);

        $account->markAsRead($project, now()->subMinutes(30));

        // Feature comment from other account
        Comment::factory()
            ->for($project)
            ->for($other_account)
            ->for($project->features->first(), 'commentable')
            ->create([
                'created_at' => now()->subMinutes(0),
            ]);

        // Requirement comment from other account
        Comment::factory()
            ->for($project)
            ->for($other_account)
            ->for($project->requirements->first(), 'commentable')
            ->create();
    }
}
