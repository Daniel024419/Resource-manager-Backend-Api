<?php

namespace Database\Factories\V1\Employee;

use App\Models\V1\User\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $timezones = [
            'Africa/Accra',
            'Europe/Berlin',
            'Africa/Kigali',
        ];

        $profiles = [
            'public/images/profile/660d13e7d2eb1.png',
            'public/images/profile/jqXWKypVduRv2RKZb4BtlU02bKB0ehyjegOrLWIb.jpg',
            'public/images/profile/660d11995e128.png',
            'public/images/profile/660d1d630d56b.png',
            'public/images/profile/660d1ecbc9f9b.png',
            'public/images/profile/8ysTH31LjoMeVWfVhNei7OC1NJVZTelYeyFCqDGF.png'
        ];

        return [
            'userId' => function () {
                return User::factory()->create()->id;
            },
            'refId' => $this->faker->unique()->uuid,
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'profilePicture' => $this->faker->randomElement($profiles),
            'phoneNumber' => $this->faker->phoneNumber,
            'bookable' => true,
            'addedBy' => 2,
            'location' => $this->faker->city,
            'timeZone' => $this->faker->randomElement($timezones), // Select a random timezone from the array
            'roleId' => 1,
        ];
    }
}