<?php

namespace Database\Factories\V1\Project;

use App\Models\V1\Project\Project;
use App\Models\V1\Client\Client;
use App\Models\V1\Employee\Employee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{

    public function definition(): array
    {
        // Create a client using its factory
        $client = Client::factory()->create();
    
        $name = $this->faker->word;
        $startDate = $this->faker->dateTimeBetween('now', '+1 year');
        $endDate = Carbon::instance($startDate)->add($this->faker->randomElement([
            '+1 month', '+2 months', '+3 months', '+4 months', '+5 months', '+6 months', '+7 months', '+8 months', '+9 months', '+10 months', '+11 months', '+12 months'
        ]));     
    
        return [
            'projectId' => $this->faker->unique()->uuid,
            'name' => $name,
            'projectCode' => $this->generateProjectCode($name),
            'client_id' => $client->id,
            'billable' => $this->faker->boolean,
            'details' => $this->faker->sentence,
            'projectType' => $this->faker->randomElement(['internal', 'external']),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'createdBy' => Employee::all()->random()->id,
        ];
    }
    protected function generateProjectCode(string $name): string
    {
        $abbreviation = substr($name, 0, 2);
        $number = $this->faker->numberBetween(1000, 9999);
        return $number . Str::upper($abbreviation);
    }
}
