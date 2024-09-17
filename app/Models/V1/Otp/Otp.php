<?php

namespace App\Models\V1\Otp;

use App\Models\V1\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    // Define the relationship to the User model (assuming a one-to-many relationship)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}