<?php

namespace App\Models\V1\Specialization;

// Importing the necessary classes
use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The UserSpecialization model represents the specialization information of a user.
 * It extends the Eloquent Model class and uses the HasFactory trait.
 */
class UserSpecialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'specialization_id'
    ];

    // [ 'BU'=>['can delete users', 'allow] ]


    /**
     * Define a relationship to the Specialization model.
     * This represents the specialization associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance linking this model to the Specialization model.
     */
    public function specializationInfo()
    {
        // This relationship indicates that a UserSpecialization "belongs to" a Specialization.
        // It establishes a direct relationship between this model and the Specialization model.
        return $this->belongsTo(Specialization::class, 'specialization_id', 'id');
    }

    /**
     * Define a relationship to the Employee model.
     * This represents the employee associated with the specialization.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance linking this model to the Employee model.
     */
    public function employee()
    {
        // This relationship indicates that a UserSpecialization "belongs to" an Employee.
        // It establishes a direct relationship between this model and the Employee model.
        return $this->belongsTo(Employee::class,'employee_id','id');
    }
}