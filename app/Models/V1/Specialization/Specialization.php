<?php

namespace App\Models\V1\Specialization;

// Import necessary Laravel classes

use App\Models\V1\skill\Skill;
use App\Models\V1\UserGroup\Group;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The Specialization model represents the different specializations that can be assigned to employees.
 * It extends Laravel's Eloquent Model class and uses the HasFactory trait.
 */
class Specialization extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 
    ];

    /**
     * Defines a one-to-many relationship with the UserSpecialization model.
     * This signifies that each specialization can be associated with many user specializations.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Returns a HasMany relationship instance, establishing this connection.
     */
    public function employees()
    {
        // The relationship indicates that one Specialization can be linked to many entries in the UserSpecialization model.
        // This function allows for the retrieval of all the UserSpecialization instances related to this Specialization.
        return $this->hasMany(UserSpecialization::class);
    }

    public function groups(){
        return $this->morphMany(Group::class,'groupable','groupableType','groupableId');
    }
 
    /**
     * Get the skills for the specialization.
     */
    public function skills()
    {
        return $this->hasMany(Skill::class, 'specializationId');
    }
}