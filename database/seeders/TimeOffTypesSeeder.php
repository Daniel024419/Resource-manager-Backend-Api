<?php

namespace Database\Seeders;

use App\Models\V1\TimeOff\TimeOffType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeOffTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createLeaveTypeIfNotExists('Annual Leave');
    
        $this->createLeaveTypeIfNotExists('Maternity Leave', [
            'duration' => 90,
            'showProof' => true
        ]);
    
        $this->createLeaveTypeIfNotExists('Bereavement Leave', [
            'showProof' => true
        ]);
    
        $this->createLeaveTypeIfNotExists('Sick Leave', [
            'showProof' => true
        ]);
    }
    


    /**
     * Create a leave type if it doesn't exist.
     *
     * @param  string  $typeName
     * @param  array|null  $attributes
     * @return void
     */
    private function createLeaveTypeIfNotExists(string $typeName, ?array $attributes = null)
    {
        $typeName = strtolower($typeName);

        if (!TimeOffType::whereRaw('LOWER(name) = ?', [$typeName])->first()) {
            $attributes['name'] = ucfirst($typeName);
            TimeOffType::create($attributes);
        }
    }
}
