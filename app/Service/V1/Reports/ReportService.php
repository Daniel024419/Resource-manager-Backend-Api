<?php

namespace App\Service\V1\Reports;

use DateTime;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use App\Models\V1\Holiday\Holiday;
use Illuminate\Support\Facades\Log;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Http\Resources\Client\ClientsReports;
use App\Repository\V1\Client\ClientInterfaceRepository;
use App\Http\Resources\Project\ProjectsReports;
use App\Repository\V1\Project\ProjectInterfaceRepository;
use App\Repository\V1\TimeOff\TimeOffRepositoryInterface;
use App\Http\Resources\Project\UtilizationReports;
use App\Repository\V1\Employee\EmployeeInterfaceRepository;
use App\Service\V1\Reports\ReportServiceInterfaceService;

class ReportService implements ReportServiceInterfaceService
{

    public  $totalProjectHoursWorkedByEmployees = 0;
    public  $totalBillableHours = 0;
    public  $totalNoneBillableHours = 0;
    public  $nonBillableHours = 0;
    public  $billableHours = 0;
    public  $totalavailablehours = 0;
    public  $capacityUsed = 0;
    public  $capacityLeft = 0;
    public  $holidaysHours = [];
    protected $projectRepository,  $notificationService,
        $clientRepository, $notificationRepository, $userRepository, $ClientRepository, $EmployeeRepository, $TimeOffRepository;
    /**
     * ProjectService constructor.
     *
     * Initializes a new instance of the ProjectService class.
     *
     * @param ProjectInterfaceRepository $projectRepository
     * @param UserInterfaceRepository $userRepository
     *     An instance of UserRepository, providing access to user-related data.
     * @param ClientInterfaceRepository $ClientRepository
     *     An instance of ClientRepository, providing access to employee-related data.
     * * @param EmployeeInterfaceRepository $EmployeeRepository
     *
     */
    public function __construct(
        ProjectInterfaceRepository $projectRepository,
        UserInterfaceRepository $userRepository,
        ClientInterfaceRepository $ClientRepository,
        EmployeeInterfaceRepository $EmployeeRepository,
        TimeOffRepositoryInterface $TimeOffRepository,


    ) {
        $this->projectRepository = $projectRepository;
        $this->userRepository = $userRepository;
        $this->ClientRepository = $ClientRepository;
        $this->EmployeeRepository = $EmployeeRepository;
        $this->TimeOffRepository = $TimeOffRepository;
    }


    /**
     * Retrieves basic reports for the user.
     * These reports include utilization and contribution metrics.
     * Utilization metrics provide insights into the user's time management,
     * while contribution metrics detail the user's involvement in projects.
     *
     * @return array An array containing utilization and contribution data for the user.
     */
    public function basicUser(): array
    {

        try {
            //pass the data for query
            $employeeProjects = $this->projectRepository->basicUser();

            $utilizationMetrics = $this->retrieveUtilizationMetrics($employeeProjects);

            $contributionMetrics = $this->retrieveContributionMetrics($employeeProjects);

            $projectStatus = $this->getProjectsByStatus($employeeProjects);


            $reports = [
                'utilization' => $utilizationMetrics,
                'contribution' => $contributionMetrics,
                'projectStatus' => $projectStatus,
            ];
            return [
                'reports' => $reports,
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            // Other exceptions
            return [
                'reposts' => [],
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieves utilization metrics grouped by year and month for the user based on employee projects.
     *
     * @param  $employeeProjects
     * @return array Utilization metrics for the user grouped by year and month.
     */
    private function retrieveUtilizationMetrics($employeeProjects)
    {
        $utilizationMetrics = [];
        try {
            foreach ($employeeProjects as $employeeProject) {
                $workHours = $employeeProject->workHours;
                $createdAt = $employeeProject->created_at;
                $yearMonthKey = $createdAt->format('Y-m');

                if (!isset($utilizationMetrics[$yearMonthKey])) {
                    $utilizationMetrics[$yearMonthKey] = [
                        'title' => 'Utilization Metrics',
                        'data' => [
                            'totalWorkHours' => 0,
                        ],
                    ];
                }

                $utilizationMetrics[$yearMonthKey]['data']['totalWorkHours'] += $workHours;
            }

            return $utilizationMetrics;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Retrieves contribution metrics.
     *
     * @param  $employeeProjects
     * @return array Contribution metrics for the user.
     */


    private function retrieveContributionMetrics($employeeProjects)
    {
        $contributions = [];
        $overAllClockedTime = 0.0;

        try {
            foreach ($employeeProjects as $employeeProject) {
                $projectName = $employeeProject->project->name;
                $workHours = $employeeProject->workHours;
                $projectContributionData = [];
                $totalHourDifference = 0;
                $overClockedHours = 0;
                $project = $employeeProject->project;

                $assignDuration = $this->calculateAssignmentsDuration($employeeProject, $project);

                // $timeTracks = $employeeProject->employee->timeTracks->where('employeeProjectId', $employeeProject->id);
                $timeTracks = $employeeProject->employee->timeTracks->where('employeeProjectId', $employeeProject->id)->reject(function ($timeTrack) {
                    // Get the date of the time track
                    $date = Carbon::parse($timeTrack->date)->startOfDay();

                    return $date->isWeekend();
                });
                foreach ($timeTracks as $timeTrack) {
                    $startTime = new DateTime($timeTrack->startTime);
                    $endTime = new DateTime($timeTrack->endTime);
                    $diff = $startTime->diff($endTime);
                    $totalDiffInHours = $diff->h + ($diff->i / 60);
                    $totalHourDifference += $totalDiffInHours;
                    $overAllClockedTime += $totalDiffInHours;

                    // Check if the total clocked hours exceed the total assigned project hours
                    if ($totalDiffInHours > $assignDuration['totalProjectUtilizationHoursPerEmployee']) {
                        // Calculate the over clocked hours
                        $overClockedHours += ($totalDiffInHours - $assignDuration['totalProjectUtilizationHoursPerEmployee']);
                    }

                    $totalDiffInHoursRounded = round($totalDiffInHours, 1);
                    $totalDiffFormatted = $diff->format('%H:%I');

                    $projectContributionData[] = [
                        'task' => $timeTrack->task,
                        'date' => $timeTrack->date,
                        'startTime' => $startTime->format('H:i:s'),
                        'endTime' => $endTime->format('H:i:s'),
                        'totalDiffFormatted' => $totalDiffFormatted,
                        'totalDiffInHours' => $totalDiffInHoursRounded,
                    ];
                }

                $contributions[] = [
                    'projectName' => $projectName,
                    'workHours' => $workHours,
                    'billable' => $project->billable,
                    'totalAssignedProjectHours' => $assignDuration['totalProjectUtilizationHoursPerEmployee'],
                    'totalHourDifference' => round($totalHourDifference, 1),
                    'overClockedHours' => round($overClockedHours, 1), // Total over clocked hours for the project
                    'timeTracks' => $projectContributionData,
                ];
            }

            // $contributions[] = ['overAllClockedTime' => round($overAllClockedTime, 1)];

            return array_values($contributions);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get active, pending, and completed projects based on the provided start date.
     *
     * @param array $employeeProjects The projects associated with the user.
     * @return array An array containing active, pending, and completed projects.
     */
    public function getProjectsByStatus($employeeProjects)
    {
        // Initialize arrays to store projects by status
        $all = [];
        $activeProjects = [];
        $pendingProjects = [];
        $completedProjects = [];
        $currentDate = Carbon::now();

        try {

            foreach ($employeeProjects as $employeeProject) {
                $project = $employeeProject->project;

                $assignDuration = $this->calculateAssignmentsDuration($employeeProject, $project);

                $createdBy = $project->createdByEmployee->firstName . ' ' . $project->createdByEmployee->lastName;

                $projectDetails = [
                    'name' => $project->name,
                    'details' => ucfirst($project->details),
                    'startDate' => Carbon::parse($project->startDate)->format('M j, Y'),
                    'endDate' => Carbon::parse($project->endDate)->format('M j, Y'),
                    'createdBy' => $createdBy,
                    'assignedAt' => Carbon::parse($employeeProject->created_at)->format('M j, Y'),
                    'workHours' => $employeeProject->workHours,
                    'totalProjectHours' => $assignDuration['totalProjectUtilizationHoursPerEmployee'],
                    'duration' => $assignDuration['durationInDays'],
                ];

                // Check project status based on start and end dates
                if (Carbon::parse($project->startDate)->lte($currentDate) &&  Carbon::parse($project->endDate)->gte($currentDate)) {
                    $activeProjects[] = $projectDetails;
                } elseif (Carbon::parse($project->startDate)->isFuture()) {
                    $pendingProjects[] = $projectDetails;
                } elseif (Carbon::parse($project->endDate)->isPast()) {
                    $completedProjects[] = $projectDetails;
                }

                $all[] = $projectDetails;
            }

            return [
                'all' => $all,
                'active' =>  $activeProjects,
                'pending' => $pendingProjects,
                'completed' => $completedProjects,
            ];
        } catch (Exception $e) {
            return [];
        }
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

        $excludedWords = ['Equinox', 'Solstice', 'start', 'holy'];

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
            // Check if the current day is a weekday (Monday to Friday) and not a holiday
            $dateString = $date->format('Y-m-d');
            if ($date->isWeekday() && !in_array($dateString, $filteredHolidays)) {
                // Add the weekday to the array
                $weekdays[] = $date->copy();
            }
        }

        $durationInDays = count($weekdays);

        $totalProjectUtilizationHoursPerEmployee = $employeeProject->workHours * $durationInDays;
        $originalDurationInDays = Carbon::parse($startDate)->diffInWeekdays(Carbon::parse($endDate)) + 1;

        $totalAvailableHours = $employeeProject->workHours * $originalDurationInDays;


        return [
            'durationInDays' => $durationInDays,
            'totalProjectUtilizationHoursPerEmployee' => $totalProjectUtilizationHoursPerEmployee,
            'totalAvailableHours' => $totalAvailableHours,
        ];
    }



    /**
     * Retrieves clients reports .
     *
     * @return array An array reports on clients.
     */
    public function clients(): array
    {
        try {

            $clients = $this->ClientRepository->clientReports();

            return [
                'reports' => ClientsReports::collection($clients),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'reposts' => [],
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieves projects reports .
     *
     * @return array An array reports on projects.
     */
    public function projects(): array
    {
        try {

            $projects = $this->projectRepository->projectReports();

            return [
                'reports' => ProjectsReports::collection($projects),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'reposts' => [],
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieves utilization reports .
     *
     * @return array An array reports on clients.
     */
    public function utilization(): array
    {
        try {

            $utilization = $this->projectRepository->utilization();

            $allSkills = collect($utilization)->pluck('projectRequirement')->flatten(1)->pluck('skill');

            // Count the occurrences of each skill
            $skillCounts = $allSkills->countBy();

            // Get the total count of all skills with counts greater than 1
            $totalSkills = $skillCounts->sum();

            // Calculate the percentage of each skill
            $skillPercentages = $skillCounts->map(function ($count) use ($totalSkills) {
                return ($count / $totalSkills) * 100;
            });

            // Output the count and percentage of each skill
            $filteredSkillData = $skillCounts->map(function ($count, $skill) use ($skillPercentages) {
                $percentage = $skillPercentages[$skill];
                return [
                    'skill' => ucfirst($skill),
                    'count' => $count,
                    'percentage' => round($percentage, 1)
                ];
            });

            $skillUtilizationOnProjects = [];
            $employeesSkills  = [];


            foreach ($utilization as $key => $project) {

                $projectSkillRequirement = collect($project->projectRequirement)->pluck('skill')->toArray();
            
                $employeesWorkHoursPerDayOnProject = 0;

                foreach ($project->employeeProjects as $employeeProject) {

                    $employeesSkills[] = collect($employeeProject->employee->skills)->pluck('name');

                    $assignDuration = $this->calculateAssignmentsDuration($employeeProject, $project);

                    $employeesWorkHoursPerDayOnProject = $assignDuration['totalProjectUtilizationHoursPerEmployee'];

                    $this->totalProjectHoursWorkedByEmployees += $employeesWorkHoursPerDayOnProject;
                    $this->totalavailablehours += $assignDuration['totalAvailableHours'];


                    if ($project->billable) {
                        $this->billableHours += $employeesWorkHoursPerDayOnProject;
                        $this->totalBillableHours += $employeesWorkHoursPerDayOnProject;
                    } else {
                        $this->nonBillableHours += $employeesWorkHoursPerDayOnProject;
                        $this->totalNoneBillableHours += $employeesWorkHoursPerDayOnProject;
                    }
                }

                // Calculate the percentage used
                if (($this->totalProjectHoursWorkedByEmployees + $this->totalavailablehours) != 0) {
                    $capacityUsed = ($this->totalProjectHoursWorkedByEmployees / $this->totalavailablehours) * 100;
                } else {
                    $capacityUsed = 0;
                }

                $skillCounts = [];
                $totalProjects = count($employeesSkills);
                
                // Initialize counts for each skill requirement
                $skillCounts = array_fill_keys($projectSkillRequirement, 0);
                
                // Create hashmap for faster search
                $employeeSkillsMap = [];
                foreach ($employeesSkills as $employeeSkills) {
                    foreach ($employeeSkills as $employeeSkill) {
                        $employeeSkillLower = strtolower($employeeSkill);
                        $employeeSkillsMap[$employeeSkillLower] = true;
                    }
                }
                
                // Iterate through project skill requirements
                foreach ($projectSkillRequirement as $skillRequirement) {
                    $skillRequirementLower = strtolower($skillRequirement);
                    // Check if the skill requirement exists in employee skills hashmap
                    if (isset($employeeSkillsMap[$skillRequirementLower])) {
                        // If it does, increment count
                        $skillCounts[$skillRequirement]++;
                    }
                }
                $skillResult=[];
                // Calculate percentage for each skill
                foreach ($skillCounts as $skill => $count) {
                    $percentage = ($count / $totalProjects) * 100;
                    $skillResult[] = [
                        'skill'=>$skill,
                        'count' => $count,
                        'percentage' =>round($percentage,1),
                    ];
                }
                
              
               $skillUtilizationOnProjects[]=[
                'project' => $project->name,
                'projectSkillRequirement' => implode(',',$projectSkillRequirement),
                'skillCounts'=>$skillResult,
               ];

            }
            $totalHoursPending = 0;
            $totalHoursApproved = 0;
            $fixWorkingHours = 8;
            $totalDaysApproved = 0;
            $totalDaysPending = 0;
            $workHourSpentOnHolidays =0;

            $usersWithLeaveRequests = $this->userRepository->timeOffRequest();

            foreach ($usersWithLeaveRequests as $user) {
                $timeZone = $user['employee']['timeZone'];
                $totalDaysApproved = 0;
                $totalDaysPending = 0;
                $hoiliday =  $this->passedHolidays($timeZone);

                foreach ($user['leaveRequests'] as $request) {
                    $startDate = Carbon::parse($request['startDate']);
                    $endDate = Carbon::parse($request['endDate']);
                    $durationInDays = $endDate->diffInWeekdays($startDate) + 1;

                    if ($request['status'] === 'approved') {
                        $totalDaysApproved += $durationInDays;
                    } else {
                        $totalDaysPending += $durationInDays;
                    }
                }

                $totalHoursApproved = $totalDaysApproved * $fixWorkingHours;
                $totalHoursPending = $totalDaysPending * $fixWorkingHours;
                $workHourSpentOnHolidays = (int)$hoiliday->count() * $fixWorkingHours;

            }


            return [
                'mostUsedSkills' => $filteredSkillData->values()->toArray(),
                'skillUtilizationOnProjects' => $skillUtilizationOnProjects,
                'capacityUsedInPercentage' => round($capacityUsed, 1),
                'billableHours' => $this->totalBillableHours,
                'nonbillableHours' => $this->totalNoneBillableHours,
                'totalProjectHoursWorkedByEmployees' => $this->totalProjectHoursWorkedByEmployees,
                'totalAvailableHours' => $this->totalavailablehours,
                'unscheduledHours' => (int)$this->totalavailablehours - (int)$this->totalProjectHoursWorkedByEmployees,
                'totalTimeOffHoursApproved' => $totalHoursApproved,
                'totalTimeOffHoursPending' => $totalHoursPending,
                'workHourSpentOnHolidays' => $workHourSpentOnHolidays,

                // 'contributors' => $contributionMetrics,
                // 'reports' => UtilizationReports::collection($utilization),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {

            Log::info($e);
            return [
                'reports' => [],
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieves time off reports reports.
     *
     * @return array An array reports on clients.
     */
    public function timeOff(): array
    {
        try {

            $leaveRequestSummaries = [];
            $approvedLeaveRequests = [];
            $pendingLeaveRequests = [];
            $totalHoursPending = 0;
            $totalHoursApproved = 0;
            $fixWorkingHours = 8;
            $totalDaysApproved = 0;
            $totalDaysPending = 0;

            $usersWithLeaveRequests = $this->userRepository->timeOffRequest();

            foreach ($usersWithLeaveRequests as $user) {
                $timeZone = $user['employee']['timeZone'];
                $totalDaysApproved = 0;
                $totalDaysPending = 0;
                $hoiliday =  $this->passedHolidays($timeZone);

                $employeeName = ucfirst($user['employee']['firstName']) . ' ' . ucfirst($user['employee']['lastName']);

                foreach ($user['leaveRequests'] as $request) {
                    $startDate = Carbon::parse($request['startDate']);
                    $endDate = Carbon::parse($request['endDate']);
                    $durationInDays = $endDate->diffInWeekdays($startDate) + 1;

                    $leaveRequestDetails = [
                        'startDate' => $startDate->format('M j, Y'),
                        'endDate' => $endDate->format('M j, Y'),
                        'durationInDays' => $durationInDays,
                        'type' => ucfirst($request['typeDetail']['name']),
                    ];

                    if ($request['status'] === 'approved') {
                        $approvedLeaveRequests[] = $leaveRequestDetails;
                        $totalDaysApproved += $durationInDays;
                    } else {
                        $pendingLeaveRequests[] = $leaveRequestDetails;
                        $totalDaysPending += $durationInDays;
                    }
                }

                $totalHoursApproved = $totalDaysApproved * $fixWorkingHours;
                $totalHoursPending = $totalDaysPending * $fixWorkingHours;

                $leaveRequestSummary = [
                    'employee' => $employeeName,
                    'totalTimeOffHoursApproved' => $totalHoursApproved,
                    'totalTimeOffDaysApproved' => $totalDaysApproved,
                    'totalTimeOffHoursPending'  => $totalHoursPending,
                    'totalTimeOffDaysPending' => $totalDaysPending,
                    'holidaySpentInDays' => (int)$hoiliday->count(),
                    'holidaySpentInHours' => (int)$hoiliday->count() * $fixWorkingHours,
                    'department' => $user['employee']['department']->departmentInfo->name,
                    'location' => $user['employee']->location,
                    'timeZone' => $timeZone,
                    'leavesApproved' => $approvedLeaveRequests,
                    'leavesPending' => $pendingLeaveRequests,
                    'holidaySpent' =>array_values($hoiliday->toArray()),

                ];

                $leaveRequestSummaries[] = $leaveRequestSummary;
            }

            return [
                'reports' => $leaveRequestSummaries,
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {

            return [
                'reports' => [],
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    public function passedHolidays($employeeTimeZone)
    {
        $observableHolidays = $this->TimeOffRepository->passedHolidays();

        // Convert the array to a collection
        $observableHolidays = collect($observableHolidays);

        $passedHolidays = $observableHolidays->filter(function ($holiday) use ($employeeTimeZone) {
            $holidayDate = Carbon::parse($holiday['date'], $holiday['timeZone']);
            $currentDate = Carbon::now($employeeTimeZone);
            return $holiday['timeZone'] === $employeeTimeZone && $holidayDate->lt($currentDate);
        });

        return $passedHolidays;
    }
}
