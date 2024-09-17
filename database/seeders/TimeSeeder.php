<?php

namespace Database\Seeders;

 use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\V1\Time\TimeConfiguration;

class TimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Example of creating a new configuration entry
        TimeConfiguration::create([
            'userId' => rand(),
            'key' => 'daily_reminder_time',
            // 'value' => '07:20 PM',
            'value' => '18:41', // 19:20 is the 24-hour format equivalent of 07:20 PM
        ]);
    }
}