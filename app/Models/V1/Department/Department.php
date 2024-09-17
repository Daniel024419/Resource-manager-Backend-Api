<?php

namespace App\Models\V1\Department;

// Importing necessary classes

use App\Models\V1\UserGroup\Group;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The Department model represents the departments within the organization.
 * It extends Laravel's Eloquent Model class and uses the HasFactory trait.
 */
class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
    ];

    /**
     * Defines a one-to-many relationship with the UserDepartment model.
     * 
     * This method establishes that each Department can be associated with many UserDepartment records.
     * Essentially, it allows for the retrieval of all the UserDepartment instances related to a particular department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Returns a HasMany relationship instance, indicating that one Department can have many associated UserDepartment records.
     */
    public function employees()
    {
        // Here, the relationship indicates that a single Department can be linked to multiple records in the UserDepartment model.
        // This function is useful for fetching all employees that belong to a specific department.
        return $this->hasMany(UserDepartment::class);
    }

    public function groups(){
        return $this->morphMany(Group::class,'groupable','groupableType','groupableId');
    }
}