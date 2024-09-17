<?php

namespace Database\Seeders;

use App\Models\V1\Department\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create([
            'name' => strtoupper('Service Center'),
        ]);


        Department::create([
            'name' =>  strtoupper('Training Center'),
        ]);

        Department::create([
            'name' =>  strtoupper('Operations'),
        ]);
    }
}