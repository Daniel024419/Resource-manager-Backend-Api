<?php

namespace App\Models\V1\Client;

use App\Models\V1\Employee\Employee;
use App\Models\V1\Project\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'clientId',
        'name',
        'details',
        'createdBy'
    ];

    /**
     * Define the relationship with the Project model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class)->withTrashed();
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