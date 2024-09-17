<?php

namespace App\Models\V1\Project;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'projectId',
        'skill'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'projectId');
    }
}
