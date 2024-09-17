<?php

namespace App\Models\V1\Time;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeConfiguration extends Model
{
    use HasFactory;
    protected $fillable = ['userId','key', 'value']; // Define fillable fields

}
