<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Support\Str;
use App\Models\V1\Role\Role;
use App\Models\V1\User\User;
use App\Models\V1\skill\Skill;
use Illuminate\Database\Seeder;
use App\Enums\AdminDefaultPassword;
use App\Models\V1\Employee\Employee;
use App\Models\V1\Department\Department;
use App\Models\V1\Department\UserDepartment;
use App\Models\V1\Notification\Notification;
use App\Models\V1\Specialization\Specialization;
use App\Models\V1\Specialization\UserSpecialization;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $Administrator_auth = User::create([
            'email' => 'jacqueline.botchway@amalitech.org',
            'password' => AdminDefaultPassword::password->value,
        ]);
        $Administrator = new Employee([
            'phoneNumber' => '0547901448',
            'firstName' => 'Jacqueline',
            'lastName' => 'Botchway',
            'refId' => Str::uuid(),
            'userId' => $Administrator_auth->id,
            'roleId' => Role::where('name', Roles::ADMIN->value)->first()->id,
            'addedBy' => $Administrator_auth->id,
            'timeZone' => 'Africa/Accra'
        ]);
        $Administrator->save();
        //create a new employee specialization
        $specialization = Specialization::create([
            'name' => ucfirst('Temp Specialization'),
        ]);
        UserSpecialization::create([
            'employee_id' => $Administrator->id,
            'specialization_id' => $specialization->id,
        ]);
        //create new employee department for the administrator
        $department = Department::create([
            'name' =>  strtoupper('Temp Department'),
        ]);
        UserDepartment::create([
            'employee_id' => $Administrator->id,
            'department_id' => $department->id,
        ]);

        //create a new skill
        Skill::create([
            'employee_id' => $Administrator->id,
            'name' => ucfirst('Temp Skill'),
        ]);

        //create a new notification
        Notification::create([
            'employee_id' => $Administrator->id,
            'message' => 'Admin account was setup for you , please update your profile',
            'by' => 1,
        ]);
    }
}