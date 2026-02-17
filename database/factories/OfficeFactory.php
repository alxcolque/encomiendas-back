<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Office ' . fake()->city(),
            'city' => fake()->city(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'manager' => fake()->name(),
            'coordinates' => fake()->latitude() . ',' . fake()->longitude(),
            'status' => 'active',
        ];
    }
}
