<?php
namespace App\Service\V1\AutoAssign;

use App\Models\V1\Project\Project;

interface AutoAssignServiceInterface{

    public function autoAssign(Project $project,array $data);
}