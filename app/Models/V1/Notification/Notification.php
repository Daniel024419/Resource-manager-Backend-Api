<?php

namespace App\Models\V1\Notification;

 use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\V1\Employee\Employee;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'by',
        'read',
        'employee_id',
    ];

    /**
     * Define a relationship to the Employee model indicating who created the notification.
     * It is a 'belongs to' relationship meaning each notification is created by one employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 'by' is the foreign key in the notifications table that references the 'id' of the employees table.
     */
    public function recordMadeBy()
    {
        return $this->belongsTo(Employee::class, 'by', 'id');
    }

    /**
     * Define a relationship to the Employee model indicating who the notification is for.
     * It is a 'belongs to' relationship meaning each notification is for one employee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 'employee_id' is the foreign key in the notifications table that references the 'id' of the employees table.
     */
    public function recordMadeTo()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}