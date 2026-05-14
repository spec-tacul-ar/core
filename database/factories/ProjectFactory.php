<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->domainWord()),
            'description' => '<p>' . implode('</p><p>', $this->faker->paragraphs($this->faker->numberBetween(1, 3))) . '</p>',
        ];
    }

    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'archived_at' => now(),
        ]);
    }
}
