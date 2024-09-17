<?php

namespace App\Models\V1\UserGroup;

use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'groupId',      
        'employeeId'    
    ];

    /**
     * Get the member (employee) associated with the user group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Employee::class, 'employeeId');
    }

    /**
     * Get the group associated with the user group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'groupId');
    }
}
