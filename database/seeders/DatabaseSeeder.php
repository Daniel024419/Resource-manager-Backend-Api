<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(RoleSeeder::class);

        $this->call(DepartmentSeeder::class);

        $this->call(SepecializationSeeder::class);

        $this->call(SkillsSeeder::class);

        $this->call(UserSeeder::class);

        $this->call(TimeSeeder::class);

        $this->call(TimeOffTypesSeeder::class);

        Artisan::call('cache:clear');
    }
}