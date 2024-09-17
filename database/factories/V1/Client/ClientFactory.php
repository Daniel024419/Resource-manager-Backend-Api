<?php

namespace Database\Factories\V1\Client;

use App\Models\V1\Employee\Employee;
use App\Models\V1\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clientId' => $this->faker->unique()->uuid,
            'name' => $this->faker->company,
            'details' => $this->faker->sentence,
            'createdBy' => Employee::all()->random()->id,
        ];
    }
}
