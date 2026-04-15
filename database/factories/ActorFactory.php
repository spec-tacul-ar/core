<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ActorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => Str::plural($this->faker->jobTitle()),
            'summary' => $this->faker->paragraph(),
            'weight' => null,
        ];
    }
}
