<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => str()->slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'email' => fake()->companyEmail(),
            'phone' => null,
            'address' => null,
            'owner_id' => User::factory(),
            'is_active' => true,
        ];
    }
}
