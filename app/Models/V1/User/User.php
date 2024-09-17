<?php

namespace App\Models\V1\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\V1\Employee\Employee;
use App\Models\V1\Otp\Otp;
use App\Models\V1\TimeOff\TimeOffRequests;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Set the password attribute and hash it.
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * method to relate to employee table
     *
     *
     */
    public function employee()
    {
        return $this->hasOne(Employee::class, 'userId', 'id');
    }

    public function otp()
    {
        return $this->hasOne(Otp::class, 'user_id', 'id');
    }

    public function leaveRequests(){
        return $this->hasMany(TimeOffRequests::class,'userId');
    }
}