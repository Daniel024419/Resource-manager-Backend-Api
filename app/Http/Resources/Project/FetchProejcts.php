<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FetchProejcts extends JsonResource
{
    public function projectRequirements()
    {
        $skills = $this->projectRequirement;
        $skillsData = [];
        foreach ($skills as $skill) {
            $skillsData[] = [
                'id' => $skill->id,
                'name' => $skill->skill
            ];
        }
        return $skillsData;
    }

    public function getEmployeeDetails()
    {

        $employees = $this->employeeProjects;
        $employeeDetails = [];

        foreach ($employees as $employeeOnProject) {

            $employee = $employeeOnProject->employee;

            if ($employee) {
                $employeeDetails[] = [
                    'refId' => $employee->refId,
                    'refId' => $employee->refId,
                    'name' => ucfirst($employee->firstName) . ' ' . ucfirst($employee->lastName),
                    'picture' => env('AWS_S3_BASE_URL') . $employee->profilePicture,
                    'workHours' => $employeeOnProject->workHours,
                    'spacializations' => $employee->specializations->pluck('specializationInfo.name')[0] ?? null,

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

        $extensionsDetails = [];
        $daysAdded = 0;

        // Loop through each project history and retrieve its details.
        foreach ($this->projectHistories as $extension) {
            // Convert newDate and oldDate to Carbon instances
            $newDate = Carbon::parse($this->endDate);
            $oldDate = Carbon::parse($extension->oldDate);

            // Calculate the difference in days
            $daysDifference = $newDate->diffInWeekdays($oldDate) + 1;
            $daysAdded += $daysDifference;

            $extensionsDetails[] = [
                'referenceId' => $extension->refId,
                'oldDate' => $oldDate->format('F j, Y'),
                'newDate' => $newDate->format('F j, Y'),
                'daysDifference' => $daysDifference,
                'reason' => $extension->reason,
                'extended_by' => $extension->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
            ];
        }

        $supposedDays = 0;
        $originalEndDate = $this->projectHistories->isNotEmpty() ? Carbon::parse($this->projectHistories->first()->oldDate) : Carbon::parse($this->endDate);
        $startDate = Carbon::parse($this->startDate);
        $supposedDays = $originalEndDate->diffInWeekdays($startDate) + 1;

        return [
            'projectId' => $this->projectId ?? null,
            'name' => ucfirst($this->name) ?? null,
            'projectType' => strtoupper($this->projectType) ?? null,
            'projectCode' => strtoupper($this->projectCode) ?? null,
            'details' => $this->details ?? null,
            'billable' => $this->billable ?? null,
            'employees' => $this->getEmployeeDetails() ?? null,
            'client' => $this->client->name ?? null,
            'clientId' => $this->client->clientId ?? null,
            'requiredSkills' => $this->projectRequirements(),
            'startDate' => $this->startDate ? Carbon::parse($this->startDate)->format('F j, Y g:i A') : null,
            'endDate' => $this->endDate ? Carbon::parse($this->endDate)->format('F j, Y g:i A') : null,
            'totalProjectExtensions' => $this->projectHistories->count(),
            'totalDaysAdded' => $daysAdded,
            'supposedDays' => $supposedDays,
            'newprojectDuration' => $supposedDays + $daysAdded,
            'extensions' => $extensionsDetails,
            'created_by' => $this->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
            'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,

        ];
    }
}
