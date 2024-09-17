<?php

namespace Database\Factories\V1\Department;

use App\Models\V1\Department\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\V1\Department\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate fake data for the Department model
        return [
            'name' => strtoupper($this->faker->unique()->word),
        ];
    }
}