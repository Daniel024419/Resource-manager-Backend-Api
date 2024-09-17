<?php

namespace App\Models\V1\Department;

// Importing necessary classes
use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The UserDepartment model represents the association between employees and their departments.
 * It extends Laravel's Eloquent Model class and uses the HasFactory trait.
 */
class UserDepartment extends Model
{
    use HasFactory;


    protected $fillable = [
        'employee_id',
        'department_id',
    ];

    /**
     * Defines a relationship to the Employee model.
     * This signifies that each UserDepartment entry is related to a specific employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance linking this model to the Employee model.
     */
    public function employee()
    {
        // This relationship indicates that a UserDepartment "belongs to" an Employee.
        // It establishes a direct relationship between this model and the Employee model.
        return $this->belongsTo(Employee::class);
    }

    /**
     * Defines a relationship to the Department model.
     * This signifies that each UserDepartment entry is related to a specific department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance linking this model to the Department model.
     */
    public function departmentInfo()
    {
        // This relationship indicates that a UserDepartment "belongs to" a Department.
        // It establishes a direct relationship between this model and the Department model.
        return $this->belongsTo(Department::class,'department_id','id');
    }
}