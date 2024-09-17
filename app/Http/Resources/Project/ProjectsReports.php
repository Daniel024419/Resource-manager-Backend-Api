<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\V1\Holiday\Holiday;
use Illuminate\Http\Resources\Json\JsonResource;


class ProjectsReports extends JsonResource
{

    public  $totalProjectHoursWorkedByEmployees = 0;
    public  $totalBillableHours = 0;
    public  $totalNoneBillableHours = 0;
    public  $nonBillableHours = 0;
    public  $billableHours = 0;
    public  $totalavailablehours = 0;
    public  $capacityUsed = 0;
    public  $capacityLeft = 0;
    public  $totalPercentage = 100;


    /**
     *  calculates the working hours since the last assignment
     *
     * @param $employeeProject
     * @param $project
     *
     */
    private function calculateAssignmentsDuration($employeeProject)
    {
        $startDate = Carbon::parse($employeeProject->created_at)->startOfDay();

        if ($employeeProject->deleted_at !== null) {
            $endDate = Carbon::parse($employeeProject->deleted_at)->startOfDay();
        } elseif (Carbon::parse($this->endDate)->isPast()) {
            $endDate = Carbon::parse($this->endDate)->startOfDay();
        } elseif (Carbon::parse($this->endDate)->isFuture()) {
            $endDate = Carbon::now()->startOfDay();
        }


        $holidays = Holiday::where('timeZone', $employeeProject->employee->timeZone)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        $excludedWords = ['Equinox', 'Solstice', 'start', 'holy' , 'Sunday','Day After','Remembrance',
        'Valentine','Epiphany','Franco','Shrove','Carnival','Hizir','Patrick','Anniversary','Maundy','Alevitic'];

        $filteredHolidays = $holidays->reject(function ($holiday) use ($excludedWords) {
            foreach ($excludedWords as $word) {
                if (stripos($holiday->holiday, $word) !== false) {
                    return true;
                }
            }
            return false;
        })->pluck('date')->toArray();

        $weekdays = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            if ($date->isWeekday() && !in_array($dateString, $filteredHolidays)) {
                $weekdays[] = $date->copy();
            }
        }

        $durationInDays = count($weekdays);

        $totalProjectUtilizationHoursPerEmployee = $employeeProject->workHours * $durationInDays;

        $originalDurationInDays = Carbon::parse($this->startDate)->diffInWeekdays(Carbon::parse($this->endDate)) + 1;
        $totalAvailableHours = $employeeProject->workHours * $originalDurationInDays;

        return [
            'durationInDays' => $durationInDays,
            'totalProjectUtilizationHoursPerEmployee' => $totalProjectUtilizationHoursPerEmployee,
            'totalAvailableHours' => $totalAvailableHours,
        ];
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        $currentDate = Carbon::now();

        $originalDurationInDays = $startDate->diffInWeekdays($endDate, true) + 1;

        $employeeProjects = $this->employeeProjects;
        $histories = $this->projectHistories;
        $employees = 0;
        $uniqueEmployees = [];
        $employee = [];
        $extensionsDetails = [];
        $daysAdded = 0;
        $currentDate = Carbon::now();
        $employeesWorkHoursPerDayOnProject = 0;
        $perDay = 0;

        foreach ($employeeProjects as $employeeProject) {

            $assignDuration = $this->calculateAssignmentsDuration($employeeProject);

            $employeesWorkHoursPerDayOnProject = $assignDuration['totalProjectUtilizationHoursPerEmployee'];

            $this->totalProjectHoursWorkedByEmployees += $employeesWorkHoursPerDayOnProject;
            $this->totalavailablehours += $assignDuration['totalAvailableHours'];
            $perDay += $employeeProject->workHours;

            if ($this->billable) {
                $this->billableHours += $employeesWorkHoursPerDayOnProject;
                $this->totalBillableHours += $employeesWorkHoursPerDayOnProject;
            } else {
                $this->nonBillableHours += $employeesWorkHoursPerDayOnProject;
                $this->totalNoneBillableHours += $employeesWorkHoursPerDayOnProject;
            }
            $employeeName = ucfirst($employeeProject->employee->firstName . ' ' . $employeeProject->employee->lastName);
            if (!in_array($employeeProject->employee->id, $uniqueEmployees)) {
                $uniqueEmployees[] = $employeeProject->employee->id;
                $employees++;
            }
            $totalAvailableHours = $originalDurationInDays * $employeeProject->workHours;
            if($originalDurationInDays + $employeeProject->workHours !=0){
                $utilizationByEachEmployee = ($employeesWorkHoursPerDayOnProject / $totalAvailableHours) * 100;
            }else{
                $utilizationByEachEmployee = 0;
            }
            $employee[] = [
                'name' => $employeeName,
                'totalWorkHoursPerDay' => $employeeProject->workHours,
                'totalWorkHoursOnProject' => $employeesWorkHoursPerDayOnProject,
                'availableWorkHours' => $totalAvailableHours,
                'utilizationByEachEmployee' => round($utilizationByEachEmployee,1),
                'assignedAt' => Carbon::parse($employeeProject->created_at)->format('M j, Y'),
                'archived' => $employeeProject->deleted_at ? true : false,
                'archivedAt' => $employeeProject->deleted_at ? Carbon::parse($employeeProject->deleted_at)->format('M j, Y') : null,
           
            ];
        }


        // Calculate the percentage used
        if (($this->totalProjectHoursWorkedByEmployees + $this->totalavailablehours) != 0) {
            $capacityUsed = ($this->totalProjectHoursWorkedByEmployees / $this->totalavailablehours) * 100;
        } else {
            $capacityUsed = 0;
        }


        if ($histories !== null) {
            foreach ($histories as $extension) {
                // Convert newDate and oldDate to Carbon instances
                $newDate = Carbon::parse($extension->newDate);
                $oldDate = Carbon::parse($extension->oldDate);

                // Calculate the difference in days
                $daysDifference = $newDate->diffInWeekdays($oldDate);
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
        }

        $originalEndDate = $histories->isNotEmpty() ? Carbon::parse($histories->first()->oldDate) : Carbon::parse($this->endDate);

        $supposedDays = $originalEndDate->diffInWeekdays(Carbon::parse($startDate)) + 1;

        // Calculate the current extension or delay
        $extension = $currentDate->diffInWeekdays(Carbon::parse($endDate), true) + 1; // Positive value for extension, negative for delay


        return [
            'name' => ucfirst($this->name),
            'projectType' => ucfirst($this->projectType),
            'projectCode' => ucfirst($this->projectCode),
            'startDate' => Carbon::parse($this->startDate)->format('M j, Y'),
            'endDate' => Carbon::parse($this->endDate)->format('M j, Y'),
            'client' => ucfirst($this->client->name),
            'totalEmployees' => $employees,
            'billable' => $this->billable ? "Yes" : "No",
            'billableHours' => $this->billableHours,
            'nonBillableHours' => $this->nonBillableHours,
            'durationInDays' => $originalDurationInDays,
            'archived' => $this->deleted_at ? true : false,
            'archivedAt' => $this->deleted_at ? Carbon::parse($this->deleted_at)->format('M j, Y') : null,
            'totalProjectHoursWorkedByEmployees' => $this->totalProjectHoursWorkedByEmployees,
            'supposedProjectHours' =>  (int)$perDay * $originalDurationInDays,
            'capacityUsedInPercentage' => min(100, max(0, round($capacityUsed, 1))),
            'utilizationSpentInHours' => $this->totalavailablehours,
            'spunAfterOriginalEndDate' => max(0, Carbon::now()->diffInWeekdays(Carbon::parse($this->endDate), false) + 1),
            'originalEndDate' => $originalEndDate->format('F j, Y'),
            'totalDaysAdded' => $daysAdded,
            'extensionType' => $extension >= 0 ? 'Extension' : 'Delay',
            'newprojectDuration' => $supposedDays + $daysAdded,
            'supposedDays' => $supposedDays,
            'employees' => $employee,
            'history' => $extensionsDetails,
            'originalEndDate' => $originalEndDate->format('F j, Y'),

        ];
    }
}