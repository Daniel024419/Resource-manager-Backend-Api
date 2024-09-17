<?php

namespace App\Models\V1\Project;

use App\Models\V1\Employee\Employee;
use App\Models\V1\TimeTracking\TimeTracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'project_id',
        'workHours',
    ];

    /**
     * Define a belongsTo relationship with the Employee model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Define a belongsTo relationship with the Project model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    /**
     * Define a hasMany relationship with the TimeTracking model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeTracks(){
        return $this->hasMany(TimeTracking::class,'employeeProjectId');
    }

   
}