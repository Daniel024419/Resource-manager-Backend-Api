<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FetchUserProjects extends JsonResource
{
    public function getEmployeeDetails()
    {

        $projects = $this->project;
        $employeeDetails = [];

        foreach ($projects->employeeProjects as $employeeProject) {
            $employeeDetails[] = [
                'projectName' => ucfirst($projects->name) ?? null,
                'name' => ucfirst ($employeeProject->employee->firstName) .' '. ucfirst ($employeeProject->employee->lastName)?? null,
                'specializations' => $employeeProject->employee->specializations->pluck('specializationInfo.name')[0] ?? null,
                'profilePicture' => env('AWS_S3_BASE_URL') . $employeeProject->employee->profilePicture ?? null,
            ];
        }

        return $employeeDetails;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'projectId' => $this->project->projectId ?? null,
            'name' => ucfirst($this->project->name) ?? null,
            'employees' => $this->getEmployeeDetails() ?? null,
            'client' => $this->project->client->name ?? null,
            'workHours'=>$this->project->employeeProjects[0]->workHours ?? 0,
            'startDate' => $this->project->startDate ? Carbon::parse($this->project->startDate)->format('M j, Y') : null,
            'endDate' => $this->project->endDate ? Carbon::parse($this->project->endDate)->format('M j, Y') : null,

        ];
    }
}