<?php

namespace App\Models\V1\Employee;

use App\Models\V1\Client\Client;
use App\Models\V1\Department\UserDepartment;
use App\Models\V1\Notification\Notification;
use App\Models\V1\Project\EmployeeProject;
use App\Models\V1\Project\Project;
use App\Models\V1\Role\Role;
use App\Models\V1\skill\Skill;
use App\Models\V1\Specialization\UserSpecialization;
use App\Models\V1\TimeTracking\TimeTracking;
use App\Models\V1\TimeOff\TimeOffRequests;
use App\Models\V1\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * The Employee model class represents employees in the application.
 * It extends the Eloquent Model class and uses several traits to add additional functionality.
 */
class Employee extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'userId',
        'refId',
        'firstName',
        'lastName',
        'email',
        'profilePicture',
        'phoneNumber',
        'location',
        'timeZone',
        'bookable',
        'roleId',
        'password',
        'addedBy',
    ];

    /**
     * Define a relationship between Employee and User.
     * Each Employee is associated with a User entity through 'userId'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance representing the user associated with the employee.
     */
    public function authInfo()
    {
        // This relationship indicates that an Employee "belongs to" a User.
        // 'userId' in the Employee model is a foreign key that references the 'id' on the User model.
        return $this->belongsTo(User::class, 'userId', 'id');
    }

    /**
     * Define a many-to-many relationship between Employee and UserSpecialization.
     * An Employee can have multiple specializations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Returns a HasMany relationship instance.
     */
    public function specializations()
    {
        // This relationship indicates that an Employee "has many" UserSpecializations.
        // 'employee_id' is the foreign key in the 'UserSpecializations' table.
        return $this->hasMany(UserSpecialization::class, 'employee_id', 'id');
    }

    /**
     * Define a many-to-many relationship between Employee and Skill.
     * An Employee can have multiple skill.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Returns a HasMany relationship instance.
     */
    public function skills()
    {
        // This relationship indicates that an Employee "has many" Skills.
        // 'employee_id' is the foreign key in the 'Skills' table.
        return $this->hasMany(Skill::class, 'employee_id', 'id');
    }


    /**
     * Define a relationship between Employee and UserDepartment.
     * An Employee belongs to one department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance representing the department associated with the employee.
     */
    public function department()
    {
        // This relationship indicates that an Employee "belongs to" a UserDepartment.
        // 'employee_id' in the UserDepartment model is a foreign key that references the 'id' on the Employee model.
        return $this->belongsTo(UserDepartment::class, 'id', 'employee_id');
    }

    /**
     * Define a relationship between Employee and Role.
     * Each Employee is associated with a role entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns a BelongsTo relationship instance representing the role associated with the employee.
     */
    public function role()
    {
        // This relationship indicates that an Employee "belongs to" a Role.
        // 'roleId' in the Employee model is a foreign key that references the 'id' in the Role model.
        return $this->belongsTo(Role::class, 'roleId', 'id');
    }

    /**
     * Define a one-to-many relationship with the Notification model.
     *
     * This method establishes that an employee can have many notifications.
     * It is a 'has many' relationship, meaning that each employee can be associated with multiple notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 'employeeId' is the foreign key in the notifications table that references the 'id' of the employee.
     * This indicates that multiple notifications can link back to one employee.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'employee_id', 'id');
    }

    /**
     * Define the relationship: an employee has many clients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clientsCreated()
    {
        return $this->hasMany(Client::class, 'createdBy', 'id');
    }

    /**
     * Define the relationship: an employee has many projects.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectCreated()
    {
        return $this->hasMany(Project::class, 'createdBy', 'id');
    }

    /**
     * Define a hasMany relationship with the EmployeeProject model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function employeeProjects()
    {
        return $this->hasMany(EmployeeProject::class);
    }

    /**
     * Define a hasMany relationship with the soft-deleted EmployeeProject model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function softDeletedEmployeeProjects()
    {
        return $this->hasMany(EmployeeProject::class)->onlyTrashed();
    }

    /**
     * Define the relationship: a client belongs to an employee (creator).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByEmployee()
    {
        return $this->belongsTo(Employee::class, 'addedBy', 'id');
    }

    /**
     * Define a hasMany relationship with the TimeTracking model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeTracks(){
        return $this->hasMany(TimeTracking::class,'employeeId');
    }
}