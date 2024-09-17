<?php

namespace Database\Factories\V1\Notification;

use Illuminate\Support\Str;
use App\Models\V1\Skill\Skill;
use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\V1\Skill\Skill>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $employee = Employee::factory()->create();
        return [
            'message' => Str::random(20),
            'by' => $employee->id,
            'employee_id' => $employee->id,
        ];
    }
}