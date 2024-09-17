<?php

namespace App\Models\V1\Project;

use Illuminate\Support\Str;
use App\Models\V1\Client\Client;
use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projectId',
        'name',
        'projectCode',
        'client_id',
        'billable',
        'details',
        'projectType',
        'startDate',
        'endDate',
        'createdBy',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->projectId = Str::uuid();
        });
    }

    /**
     * Define the relationship with the Client model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    /**
     * Define the relationship: a client belongs to an employee (creator).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdByEmployee()
    {
        return $this->belongsTo(Employee::class, 'createdBy', 'id');
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
     * Define a hasMany relationship with the project history model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectHistories()
    {
        return $this->hasMany(ProjectHistory::class, 'projectId')->withTrashed();
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
     * Define a hasMany relationship with the ProjectRequirement model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectRequirement()
    {
        return $this->hasMany(ProjectRequirement::class, 'projectId');
    }

    public function getRouteKeyName()
    {
        return 'projectId';
    }
}