<?php

namespace App\Models\V1\skill;

use App\Models\V1\Employee\Employee;
use App\Models\V1\Specialization\Specialization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'employee_id',
        'rating',
        'specializationId'
    ];

    
    /**
     * Get the specialization that owns the skill.
     */
    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'specializationId');
    }

    /**
     * Get the specialization that owns the skill.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

}