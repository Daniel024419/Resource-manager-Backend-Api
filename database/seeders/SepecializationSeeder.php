<?php

namespace Database\Seeders;

use App\Models\V1\Specialization\Specialization;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SepecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Specialization::create([
            'name'=> strtoupper('Frontend Developer'),
        ]);

        Specialization::create([
        'name'=> strtoupper( 'Backend Developer'),
        ]);

        Specialization::create([
            'name'=> strtoupper( 'UI/UX Designer'),
        ]);

        Specialization::create([
            'name'=> strtoupper( 'DevOps'),
        ]);

        Specialization::create([
            'name'=> strtoupper( 'Data Scientist'),
        ]);

        Specialization::create([
            'name'=> strtoupper( 'Software Tester'),
        ]);
    }
}