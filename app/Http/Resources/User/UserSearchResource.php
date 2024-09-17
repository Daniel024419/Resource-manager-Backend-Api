<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Skills\SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Project\EmployeeProjectResource;
use App\Http\Resources\Specialization\SpecializationResource;
use Carbon\Carbon;

class UserSearchResource extends JsonResource
{

    public function getWorkProjectDetails()
    {
        $employeeProjects = $this->employee->employeeProjects;

        $workHoursDetails = [];
        if ($employeeProjects) {
            foreach ($employeeProjects as $project) {
                $workHoursDetails[] = [
                    'name' => ucfirst($project->project->name) ?? null,
                    'projectCode' => $project->project->projectCode ?? null,
                    'client' => $project->project->client->name ?? null,
                    'startDate' => Carbon::parse($project->project->startDate)->toIso8601String(),
                    'endDate' => Carbon::parse($project->project->endDate)->toIso8601String(),
                    'scheduleId' => $project->id,
                    'workHours' => $project->workHours,
                ];
            }
        }
        return $workHoursDetails;
    }
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'email' => $this->email ?? null,
            'refId' => $this->employee->refId ?? null,
            'firstName' => $this->employee->firstName ?? null,
            'lastName' => $this->employee->lastName ?? null,
            'profilePicture' => env('AWS_S3_BASE_URL') . $this->employee->profilePicture,
            'phoneNumber' => $this->employee->phoneNumber ?? null,
            'bookable' => $this->employee->bookable ?? null,
            'department' => $this->employee->department->departmentInfo->name ?? null,
            'specializations' => SpecializationResource::collection($this->employee->specializations->unique('name') ?? collect()),
            'skills' => SkillsResource::collection($this->employee->skills->unique('name') ?? collect()),
            'role' => $this->employee->role->name ?? null,
            'permissions' => new PermissionResource($this->employee->role)  ?? null,
            'project' =>  $this->getWorkProjectDetails(),
            'updated_at' => $this->employee->updated_at ? \Carbon\Carbon::parse($this->employee->updated_at)->format('F j, Y g:i A') : null,
            'created_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
        ];
    }
}
