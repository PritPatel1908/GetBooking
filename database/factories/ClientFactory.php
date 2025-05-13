<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'middle_name' => fake()->optional(0.3)->firstName(),
            'last_name' => fake()->lastName(),
            'name' => function (array $attributes) {
                return trim($attributes['first_name'] . ' ' . ($attributes['middle_name'] ? $attributes['middle_name'] . ' ' : '') . $attributes['last_name']);
            },
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'full_address' => fake()->address(),
            'area' => fake()->streetName(),
            'city' => fake()->city(),
            'pincode' => fake()->numerify('######'),
            'state' => fake()->state(),
            'country' => 'India',
            'profile_picture' => null,
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
