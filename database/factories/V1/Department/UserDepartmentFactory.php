<?php

namespace Database\Factories\V1\Department;

use App\Models\V1\Department\Department;
use App\Models\V1\Department\UserDepartment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\V1\Department\UserDepartment>
 */
class UserDepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate fake data for the UserDepartment model
        return [
            'employee_id' => $this->faker->unique()->numberBetween(1, 100),
            'department_id' => Department::all()->random()->id,
        ];
    }
}