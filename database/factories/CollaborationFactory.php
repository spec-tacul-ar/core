<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Role;
use App\Models\Account;

class CollaborationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role' => $this->faker->randomElement(Role::values()),
            'account_id' => Account::factory(),
        ];
    }
}
