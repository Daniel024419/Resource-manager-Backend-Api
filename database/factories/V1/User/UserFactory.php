<?php

namespace Database\Factories\V1\User;

use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Instantiate the RoleSeeder
        $roleSeeder = new RoleSeeder();

        // Run the seeder
        $roleSeeder->run();
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }
}