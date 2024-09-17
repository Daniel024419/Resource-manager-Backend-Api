<?php

namespace App\Models\V1\Role;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'can_add_user',
        'can_add_manager',
        'can_update_user_role',
        'can_create_project',
        'can_create_client',
        'can_assign_user_to_project',
        'can_assign_client_to_project',
        'can_assign_user_to_department',
        'can_assign_user_to_specialization',
        'can_add_user_to_group',
    ];

    protected $casts = [
        'can_add_user' => 'boolean',
        'can_add_manager' => 'boolean',
        'can_update_user_role' => 'boolean',
        'can_create_project' => 'boolean',
        'can_create_client' => 'boolean',
        'can_assign_user_to_project' => 'boolean',
        'can_assign_client_to_project' => 'boolean',
        'can_assign_user_to_department' => 'boolean',
        'can_assign_user_to_specialization' => 'boolean',
        'can_add_user_to_group' => 'boolean',
    ];
}