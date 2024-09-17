<?php

namespace App\Models\V1\UserGroup;

use App\Models\V1\Employee\Employee;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'refId',
        'name',
        'description',
        'createdBy',
        'groupableId',
        'groupableType',
    ];

    /**
     * Boot method to automatically generate a UUID for the 'refId' attribute when creating a new group.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->refId = Str::uuid();
        });
    }

    /**
     * Get the members (user groups) associated with the group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupMembers()
    {
        return $this->hasMany(UserGroup::class, 'groupId');
    }

    /**
     * Get the employee who created the group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Returns the relationship to the Employee model representing the group creator.
     */
    public function groupAdmin()
    {
        return $this->belongsTo(Employee::class, 'createdBy');
    }


    /**
     * Get the owning groupable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     * Returns the morphTo relationship to retrieve the owning groupable model.
     */
    public function groupable()
    {
        return $this->morphTo('groupable','groupableType','groupableId');
    }
}
