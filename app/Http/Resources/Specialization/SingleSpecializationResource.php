<?php

namespace App\Http\Resources\Specialization;

use App\Http\Resources\Project\EmployeeProjectResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SingleSpecializationResource extends JsonResource
{
    /**
     * Accessor for retrieving members associated with specialization.
     *
     * @return array The array of project details or an empty array if no associated projects.
     */

    public function getSpecilizationMembers()
    {
        $employees = $this->employees()->with('employee.skills', 'employee.employeeProjects.project.client', 'employee.department.departmentInfo', 'employee.role')->get();

        $groupEmployees = $employees->map(function ($employee) {
            if (!$employee->employee || !$employee->employee->employeeProjects) {
                return null;
            }

            $skillsArray = $employee->employee->skills->map(function ($skill) {
                return [
                    'id' => $skill->id,
                    'skillName' => $skill->name,
                    'rating' => $skill->rating,
                ];
            })->toArray();

            $clientDetails = $employee->employee->employeeProjects->flatMap(function ($employeeProject) {
                $project = $employeeProject->project;
                if (!$project || !$project->client) {
                    return [];
                }
                return [
                    'name' => $project->client->name,
                ];
            })->toArray();
            return [
                'id' => $employee->employee->id,
                'refId' => $employee->employee->refId,
                'name' => ucwords($employee->employee->firstName . ' ' . $employee->employee->lastName),
                'profilePicture' => env('AWS_S3_BASE_URL').$employee->employee->profilePicture,
                'phoneNumber' => $employee->employee->phoneNumber,
                'department' => optional($employee->employee->department->departmentInfo)->name,
                'role' => optional($employee->employee->role)->name,
                'bookable' => $employee->employee->bookable,
                'skills' => $skillsArray,
                'project' => $employee->employee->employeeProjects ? EmployeeProjectResource::collection($employee->employee->employeeProjects) : [],
                'client' => $clientDetails,
                'createdAt' => $employee->employee->created_at ? Carbon::parse($employee->created_at)->format('F j, Y g:i A') : null,
            ];
        })->filter();

        return $groupEmployees->values()->all();
    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'employees' => $this->getSpecilizationMembers()
        ];
    }
}
