<?php

namespace App\Models\V1\TimeOff;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TimeOffType extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "duration",
        "showProof",
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->refId = Str::uuid();
        });
    }
}