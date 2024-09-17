<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Skills\SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Specialization\SpecializationResource;
use Carbon\Carbon;
class UpdateUserInfoShowResource extends JsonResource
{

        /**
     * Accessor for retrieving projects associated with client.
     *
     * @return array The array of project details or an empty array if no associated projects.
     */
    public function getClientDetails()
    {
        $employeeProjects = $this->employeeProjects;

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

    public function getWorkProjectDetails()
    {
        $employeeProjects = $this->employeeProjects;

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
            'email' => $this->authInfo->email ?? null,
            'refId' => $this->refId ?? null,
            'firstName' => $this->firstName  ?? null,
            'lastName' => $this->lastName  ?? null,
            'phoneNumber' => $this->phoneNumber  ?? null,
            'bookable' => $this->bookable ?? null,
            'location' => $this->location ?? null,
            'timeZone' => $this->timeZone ?? null,
            'department' => $this->department->departmentInfo->name ?? null,
            'specializations' => SpecializationResource::collection($this->specializations->unique('name') ?? collect()),
            'skills' => SkillsResource::collection($this->skills->unique('name') ?? collect()),
            'role' => $this->role->name ?? null,
            'profilePicture' => env('AWS_S3_BASE_URL') . $this->profilePicture,
            'client' => $this->getClientDetails(),
            'workHours' => $this->getWorkProjectDetails(),
            'updated_at' => $this->updated_at ?? null,
            'created_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,

        ];
    }
}
