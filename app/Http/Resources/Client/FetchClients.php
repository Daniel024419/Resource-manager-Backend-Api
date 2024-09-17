<?php

namespace App\Http\Resources\Client;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class FetchClients extends JsonResource
{
    /**
     * Accessor for retrieving projects associated with client.
     *
     * @return array The array of project details or an empty array if no associated projects.
     */
    public function getProjectDetails()
    {
        // Retrieve the collection of projects associated with the client.
        $projects = $this->projects;

        $projectDetails = [];

        foreach ($projects as $project) {
            $projectDetails[] = [
                'name' => $project->name,
            ];
        }

        return $projectDetails;
    }

    /**
     * Accessor for retrieving details of employees associated with all client projects.
     *
     * @return array The array of employee details or an empty array if no associated projects.
     */
    public function getEmployeeDetails()
    {
        // Retrieve the collection of projects associated with the client.
        $projects = $this->projects;

        $employeeDetails = [];

        foreach ($projects as $project) {
            // Retrieve the employee projects associated with the project.
            $employeeProjects = $project->employeeProjects;

            // Loop through each employee project and retrieve the employee details.
            foreach ($employeeProjects as $employeeProject) {
                // Retrieve the employee associated with the employee project.
                $employee = $employeeProject->employee;

                // Check if there is an employee associated with the employee project.
                if ($employee) {
                    $employeeDetails[] = [
                        'name' => $employee->firstName,
                        'picture' => env('AWS_S3_BASE_URL').$employee->profilePicture,
                    ];
                }
            }
        }

        return $employeeDetails;
    }

    /**
     * Accessor for retrieving the total count of employees associated with all client projects.
     *
     * @return int The total count of employees or 0 if no associated projects.
     */
    public function getTotalemployees()
    {
        $employeeDetails = $this->getEmployeeDetails();

        return count($employeeDetails);
    }


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? null,
            'clientId' => $this->clientId ?? null,
            'name' => $this->name ?? null,
            'details' => $this->details ?? null,
            'totalProjects' =>$this->projects->count()  ?? null,
            'projects' => $this->getProjectDetails(),
            'employees' => $this->getEmployeeDetails(),
            'totalemployees' => $this->getTotalemployees(),
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
            'created_by' => $this->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
            'archived_on' => $this->deleted_at ? Carbon::parse($this->deleted_at)->format('F j, Y g:i A') : null,
        ];
    }
}