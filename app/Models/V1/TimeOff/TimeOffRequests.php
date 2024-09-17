<?php

namespace App\Models\V1\TimeOff;

use App\Models\V1\User\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TimeOffRequests extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "userId",
        "startDate",
        "endDate",
        "type",
        "details",
        "proof",
        "status",
        "reviewedBy",
        "canBeReviewedBy",
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->refId = Str::uuid();
        });
    }
    /**
     * Define a relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId')->withTrashed();
    }

    /**
     * Define a relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewedBy');
    }

    /**
     * Define a relationship with the User model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canBeReviewedBy()
    {
        return $this->belongsTo(User::class, 'canBeReviewedBy');
    }


    /**
     * Define a relationship with the TimeOffType model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function typeDetail()
    {
        return $this->belongsTo(TimeOffType::class, 'type');
    }
}
