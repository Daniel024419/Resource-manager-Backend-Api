<?php

namespace Database\Seeders;

use App\Enums\Roles;
use App\Models\V1\Role\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createRoleIfNotExists(
            Roles::ADMIN->value,
            [
                'can_add_user' => true,
                'can_add_manager' => true,
                'can_update_user_role' => true,
                'can_create_project' => true,
                'can_create_client' => true,
                'can_assign_user_to_project' => true,
                'can_assign_client_to_project' => true,
                'can_assign_user_to_department' => true,
                'can_assign_user_to_specialization' => true,
                'can_add_user_to_group' => true,
            ]
        );

        $this->createRoleIfNotExists(
            Roles::MGT->value,
            [
                'can_add_user' => true,
                'can_create_project' => true,
                'can_create_client' => true,
                'can_assign_user_to_project' => true,
                'can_assign_client_to_project' => true,
                'can_assign_user_to_department' => true,
                'can_assign_user_to_specialization' => true,
                'can_add_user_to_group' => true,
            ]
        );

        $this->createRoleIfNotExists(Roles::BU->value);
    }

    /**
     * Create a role if it doesn't exist.
     *
     * @param  string  $roleName
     * @param  array|null  $attributes
     * @return void
     */
    private function createRoleIfNotExists(string $roleName, ?array $attributes = null)
    {
        if (!Role::where('name', $roleName)->first()) {
            $roleAttributes = $attributes ?: [];
            $roleAttributes['name'] = $roleName;
            Role::create($roleAttributes);
        }
    }
}