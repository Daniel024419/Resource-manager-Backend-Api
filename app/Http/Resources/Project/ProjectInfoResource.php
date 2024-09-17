<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectInfoResource extends JsonResource
{
    public function getEmployeeDetails()
    {

        $employees = $this->employeeProjects;
        $employeeDetails = [];
        
        foreach ($employees as $employeeOnProject) {
            $employee = $employeeOnProject->employee;
            if ($employee) {
                $employeeDetails[] = [
                    'name' => $employee->firstName,
                    'picture' => env('AWS_S3_BASE_URL').$employee->profilePicture,
                ];
            }
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
            'projectId' => $this->projectId ?? null,
            'name' => $this->name ?? null,
            'projectType' => strtoupper($this->projectType) ?? null,
            'projectCode' => strtoupper($this->projectCode) ?? null,
            'details' => $this->details ?? null,
            'billable' => $this->billable ?? null,
            'employees' => $this->getEmployeeDetails() ?? null,
            'client' => $this->client->name ?? null,
            'startDate' => $this->startDate ? Carbon::parse($this->startDate)->format('F j, Y') : null,
            'endDate' => $this->endDate ? Carbon::parse($this->endDate)->format('F j, Y') : null,
            'created_by' => $this->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
        ];
    }
}
