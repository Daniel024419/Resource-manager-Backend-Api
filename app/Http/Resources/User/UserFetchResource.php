<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Skills\SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Project\EmployeeProjectResource;
use App\Http\Resources\Specialization\SpecializationResource;

class UserFetchResource extends JsonResource
{
    /**
     * Accessor for retrieving projects associated with client.
     *
     * @return array The array of project details or an empty array if no associated projects.
     */
    public function getClientDetails()
    {
        $employeeProjects = $this->employee->employeeProjects;

        $clientDetails = [];
        if ($employeeProjects) {
            foreach ($employeeProjects as $employeeProject) {
                $project = $employeeProject->project;

                if ($project) {
                    $client = $project->client;

                    if ($client) {
                        $clientDetails[] = [
                            'name' => $client->name,
                        ];
                    }
                }
            }
        }

        // Extract unique client names using array_unique
        $uniqueClientNames = array_unique(array_column($clientDetails, 'name'));

        // Create a new array with unique client names
        $uniqueClientDetails = [];
        foreach ($uniqueClientNames as $name) {
            $uniqueClientDetails[] = ['name' => $name];
        }

        return $uniqueClientDetails;
    }

    public function getWorkHoursDetails()
    {
        $employeeProjects = $this->employee->employeeProjects;

        $workHoursDetails = [];
        if ($employeeProjects) {
            foreach ($employeeProjects as $project) {
                $workHoursDetails[] = [
                    'hour' => $project->workHours,
                ];
            }
        }
        return $workHoursDetails;
    }


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
            'firstName' => ucfirst($this->employee->firstName ?? null),
            'lastName' => ucfirst($this->employee->lastName ?? null),
            'profilePicture' =>  env('AWS_S3_BASE_URL') . $this->employee->profilePicture ?? null,
            'phoneNumber' => $this->employee->phoneNumber ?? null,
            'bookable' => $this->employee->bookable ?? null,
            'location' => $this->employee->location ?? null,
            'timeZone' => $this->employee->timeZone ?? null,
            'department' => $this->employee->department->departmentInfo->name ?? null,
            'specializations' => SpecializationResource::collection($this->employee->specializations->unique('name') ?? collect()),
            'skills' => SkillsResource::collection($this->employee->skills->unique('name') ?? collect()),
            'role' => $this->employee->role->name ?? null,
            'permissions' => new PermissionResource($this->employee->role) ?? null,
            'project' => $this->getWorkProjectDetails(),
            'client' => $this->getClientDetails(),
            'workHours' => $this->getWorkHoursDetails(),
            'updated_at' => $this->employee->updated_at ? \Carbon\Carbon::parse($this->employee->updated_at)->format('F j, Y g:i A') : null,
            'created_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
        ];
    }
}