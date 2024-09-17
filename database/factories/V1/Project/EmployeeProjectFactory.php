<?php

namespace Database\Factories\V1\Project;

use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\V1\Project\EmployeeProject>
 */
class EmployeeProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id'=> Employee::all()->random()->id,
            'project_id' => 1,
        ];
    }
}