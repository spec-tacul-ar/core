<?php

namespace Database\Seeders;

use Spectacular\Core\Models\Feature;
use Spectacular\Core\Models\Project;
use Spectacular\Core\Models\Requirement;
use Faker\Generator as Faker;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = resolve(Faker::class);
    }

    public function run()
    {
        $project = Project::factory()
            ->state(['name' => 'My Project'])
            ->hasUsers($this->faker->numberBetween(3, 4))
            ->has(
                Feature::factory()
                    ->count($this->faker->numberBetween(4, 5))
                    ->has(
                        Requirement::factory()
                            ->count($this->faker->numberBetween(2, 4))
                            ->hasTasks($this->faker->numberBetween(0, 4))
                            ->hasUnknowns($this->faker->numberBetween(0, 2))
                            ->afterCreating(function (Requirement $requirement) {
                                $users = $this->faker->randomElements($requirement->feature->project->users, null);

                                foreach ($users as $user) {
                                    $requirement->assignments()->make()->user()->associate($user)->save();
                                }
                            })
                    )
            )
            ->create();

        Project::factory()
            ->state([
                'name' => 'Empty Project',
                'description' => null,
            ])
            ->create();
    }
}
