<?php

namespace App\Models\V1\TimeTracking;

use App\Models\V1\Project\EmployeeProject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimeTracking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "employeeProjectId",
        "employeeId",
        "task",
        "date",
        "startTime",
        "endTime",
    ];

    protected $casts = [
        'endTime' => 'datetime',
        'startTime' => 'datetime',
    ];

    /**
     * Get the employee project associated with the time tracking entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employeeProject(): BelongsTo
    {
        return $this->belongsTo(EmployeeProject::class, 'employeeProjectId', 'id');
    }
}
