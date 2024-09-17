<?php

namespace Database\Factories\V1\Specialization;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\V1\Specialization\Specialization>
 */
class SpecializationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate fake data for the Specialization model
        return [
            'name' => strtoupper($this->faker->unique()->word),
        ];
    }
}