<?php

namespace App\Models\V1\Project;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\V1\Employee\Employee;

class ProjectHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'projectId',
        'refId',
        'newDate',
        'oldDate',
        'reason',
        'createdBy'
    ];

    /**
     * Define a belongsTo relationship with the Project history model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId', 'id')->withTrashed();
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
}