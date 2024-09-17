<?php

namespace App\Http\Resources\Client;

use App\Models\V1\Holiday\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\JsonResource;


class ClientsReports extends JsonResource
{

    public $totalBillableHours = 0;
    public $totalNoneBillableHours = 0;
    public $nonBillableHours = 0;
    public $billableHours = 0;
    public $employees = 0;
    public $totalAllProjectUtilizationWorkHours = 0;
    public $totalavailablehours = 0;
    public $capacityUsed = 0;
    public $OverAllCapacityUsed = 0;
    public $totalCapacityAccumulated = 0;


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
        $uniqueEmployees = [];
        $this->totalAllProjectUtilizationWorkHours = 0;
        $this->totalavailablehours = 0;
        $this->capacityUsed = 0;
        $totalProjectHoursWorkedByEmployees = 0;
        $employee = [];
        $employeesWorkHoursPerDayOnProject = 0;

        foreach ($projects as $project) {
            $startDate = Carbon::parse($project->startDate);
            $endDate = Carbon::parse($project->endDate);
            $employeeProjects = $project->employeeProjects;
            $histories = $project->projectHistories;
            $employee = [];

            $projectHistories = $this->calculateProjecttimelines($histories, $project);
            $originalDurationInDays = $startDate->diffInWeekdays($endDate) + 1;

            foreach ($employeeProjects as $employeeProject) {
                $employeesWorkHoursPerDayOnProject += $employeeProject->workHours;
                $assignDuration = $this->calculateAssignmentsDuration($employeeProject, $project);

                $totalProjectHoursWorkedByEmployees += $assignDuration['totalProjectUtilizationHoursPerEmployee'];
                $totalWorkHoursPerEmployee = $assignDuration['totalProjectUtilizationHoursPerEmployee'];

                $this->totalAllProjectUtilizationWorkHours += $totalWorkHoursPerEmployee;
                $this->totalavailablehours += $assignDuration['totalAvailableHours'];

                if ($project->billable) {
                    $this->billableHours += $totalWorkHoursPerEmployee;
                    $this->totalBillableHours += $totalWorkHoursPerEmployee;
                } else {
                    $this->nonBillableHours += $totalWorkHoursPerEmployee;
                    $this->totalNoneBillableHours += $totalWorkHoursPerEmployee;
                }

                $employeeName = ucfirst($employeeProject->employee->firstName . ' ' . $employeeProject->employee->lastName);
                if (!in_array($employeeProject->employee->id, $uniqueEmployees)) {
                    $uniqueEmployees[] = $employeeProject->employee->id;
                    $this->employees++;
                }
                $assignedAt = Carbon::parse($employeeProject->created_at)->format('M j, Y');
                $endedAt = Carbon::parse($employeeProject->deleted_at)->format('M j, Y');
                $totalAvailableHours = $originalDurationInDays * $employeeProject->workHours;
                if($originalDurationInDays + $employeeProject->workHours !=0){
                    $utilizationByEachEmployee = ($totalWorkHoursPerEmployee / $totalAvailableHours) * 100;
                }else{
                    $utilizationByEachEmployee = 0;
                }
                $employee[] = [
                    'name' => $employeeName,
                    'totalWorkHoursPerDay' => $employeeProject->workHours,
                    'totalWorkHoursOnProject' => $totalWorkHoursPerEmployee,
                    'availableWorkHours' => $totalAvailableHours,
                    'utilizationByEachEmployee' => round($utilizationByEachEmployee,1),
                    'assignedAt' => $assignedAt,
                    'enddedAt' => $endedAt,
                    'daysAtWork'=> $assignDuration['durationInDays'],
                    'archived' => $employeeProject->deleted_at ? true : false,
                    'archivedAt' => $employeeProject->deleted_at ? Carbon::parse($employeeProject->deleted_at)->format('M j, Y') : null,
                ];
            }

            // Calculate the percentage left
            if (($this->totalAllProjectUtilizationWorkHours + $this->totalavailablehours) != 0) {
                $this->capacityUsed = ($this->totalAllProjectUtilizationWorkHours / $this->totalavailablehours) * 100;
                $this->totalCapacityAccumulated += $this->capacityUsed;
            } else {
                $this->capacityUsed = 0;
                $this->totalCapacityAccumulated = 0;
            }

            $projectDetails[] = [
                'name' => ucfirst($project->name),
                'archived' => $project->deleted_at ? true : false,
                'archivedAt' => $project->deleted_at ? Carbon::parse($project->deleted_at)->format('M j, Y') : null,
                'billable' => $project->billable,
                'extended' => $projectHistories['totalDaysAdded'] > 0 ? "Yes" : "No",
                'startDate' => Carbon::parse($project->startDate)->format('M j, Y'),
                'endDate' => Carbon::parse($project->endDate)->format('M j, Y'),
                'originalEndDate' => $projectHistories['originalEndDate'],
                'originalDurationInDays' => $originalDurationInDays,
                'originalDurationInWeeks' => $startDate->diffInWeeks($endDate),
                'totalProjectHoursWorkedByEmployees' => $totalProjectHoursWorkedByEmployees,
                'supposedProjectHours' =>  $employeesWorkHoursPerDayOnProject * $originalDurationInDays,
                'employeesWorkHoursPerDayOnProject' => $employeesWorkHoursPerDayOnProject,
                'extensionInDays' => $projectHistories['extension'],
                'extensionType' => $projectHistories['extensionType'],
                'spunAfterOriginalEndDate' => $projectHistories['spunAfterOriginalEndDate'],
                'capacityUsedInPercentage' => min(100,max(0 ,round($this->capacityUsed, 1))),
                'totalDaysAdded' => $projectHistories['totalDaysAdded'],
                'supposedDays' => $projectHistories['supposedDays'],
                'newprojectDurationInDays' => (int)$projectHistories['supposedDays'] + (int)$projectHistories['totalDaysAdded'],
                'employees' => $employee,
                'history' => $projectHistories['extensionsDetails'],
            ];
        }

        return array_values($projectDetails);
    }

    /**
     *  calculates the working hours since the last assignment
     *
     * @param $employeeProject
     * @param $project
     *
     */

    private function calculateAssignmentsDuration($employeeProject, $project)
    {
        $startDate = Carbon::parse($employeeProject->created_at)->startOfDay();

        if ($employeeProject->deleted_at !== null) {
            $endDate = Carbon::parse($employeeProject->deleted_at)->startOfDay();
        } elseif (Carbon::parse($project->endDate)->isPast()) {
            $endDate = Carbon::parse($project->endDate)->startOfDay();
        } elseif (Carbon::parse($project->endDate)->isFuture()) {
            $endDate = Carbon::now()->startOfDay();
        }

        $holidays = Holiday::where('timeZone', $employeeProject->employee->timeZone)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        $excludedWords = ['Equinox', 'Solstice', 'start', 'holy','Sunday','Day After','Remembrance',
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

        $originalDurationInDays = Carbon::parse($project->startDate)->diffInWeekdays(Carbon::parse($project->endDate)) + 1;
        $totalAvailableHours = $employeeProject->workHours * $originalDurationInDays;

        return [
            'durationInDays' => $durationInDays,
            'totalProjectUtilizationHoursPerEmployee' => $totalProjectUtilizationHoursPerEmployee,
            'totalAvailableHours' => $totalAvailableHours,
        ];
    }


    /**
     *calculates project execution time
     *@param mixed $histories
     *@param mixed $project
     */
    public function calculateProjecttimelines($histories, $project)
    {
        $daysAdded = 0;
        $extensionsDetails = [];
        $currentDate = Carbon::now();

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

        $originalEndDate = $histories->isNotEmpty() ? Carbon::parse($histories->first()->oldDate) : Carbon::parse($project->endDate);

        $supposedDays = Carbon::parse($project->startDate)->diffInWeekdays($originalEndDate) + 1;

        // Calculate the current extension or delay
        $extension = $currentDate->diffInWeekdays(Carbon::parse($project->endDate), true) + 1; // Positive value for extension, negative for delay

        return [
            'originalEndDate' => $originalEndDate->format('F j, Y'),
            'totalProjectExtensions' => $this->projectHistories ? $this->projectHistories->count() : 0,
            'totalDaysAdded' => $daysAdded,
            'extensionType' => $extension >= 0 ? 'Extension' : 'Delay',
            'extension' => $extension,
            'spunAfterOriginalEndDate' => max(0, Carbon::now()->diffInWeekdays(Carbon::parse($project->endDate), false) + 1),
            'supposedDays' => $supposedDays,
            'newprojectDuration' => $supposedDays + $daysAdded,
            'extensionsDetails' => $extensionsDetails,
        ];
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
            'details' => $this->details,            
            'archived' => $this->deleted_at ? true : false,
            'archivedAt' => $this->deleted_at ? Carbon::parse($this->deleted_at)->format('M j, Y') : null,
            'totalProjects' => $this->projects->count(),
            'projects' => $this->getProjectDetails(),
            'billableHours' => $this->billableHours,
            'nonBillableHours' => $this->nonBillableHours,
            'totalEmployees' => $this->employees,
            'overAllProjectWorkHours' => $this->totalAllProjectUtilizationWorkHours,
            'supposedProjectHours' => $this->totalavailablehours,
            'capacityUsedInPercentage' => $this->totalAllProjectUtilizationWorkHours + $this->totalavailablehours !== 0 ? min(100,max(0,round(($this->totalAllProjectUtilizationWorkHours / $this->totalavailablehours),1) * 100 )): 0,
            'created_at' => Carbon::parse($this->created_at)->format('F j, Y g:i A'),
            'created_by' => $this->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
        ];
    }
}
