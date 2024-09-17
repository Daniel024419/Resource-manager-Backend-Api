<?php

namespace Database\Factories\V1\Skill;

use App\Models\V1\Employee\Employee;
use App\Models\V1\Skill\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // $employee = Employee::factory()->create();

        return [
            'employee_id' => 1,
            'name' => $this->faker->word,
            'rating' => $this->faker->numberBetween(1, 5),
        ];
    }
}
