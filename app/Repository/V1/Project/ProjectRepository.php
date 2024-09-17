<?php

namespace App\Repository\V1\Project;

use App\Models\V1\Client\Client;
use Exception;
use Carbon\Carbon;
use App\Models\V1\User\User;
use App\Models\V1\Project\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Employee\Employee;
use App\Models\V1\Project\ProjectHistory;
use App\Models\V1\Project\EmployeeProject;
use App\Models\V1\Project\ProjectRequirement;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ProjectRepository implements ProjectInterfaceRepository
{

    /**
     * extend project time line
     * @param array $projectData
     * @return ProjectHistory|null
     * @throws ModelNotFoundException
     */
    public function extendTimeLine(array $projectData)
    {
        try {
            DB::beginTransaction();
            $project = ProjectHistory::create($projectData);
            DB::commit();
            return $project;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new ModelNotFoundException();
        }
    }

    /**
     * Get the projects assigned to the authenticated employee
     *
     * This function retrieves all employee projects and their related projects,
     * then filters out the projects with a start date that is not today or in the past.
     *
     * @return Illuminate\Support\Collection
     */
    public function projectsAssigned()
    {
        $employeeProjects = EmployeeProject::where('employee_id', auth()->user()->employee->id)
            ->with('project')
            ->get();

        $filteredProjects = $employeeProjects->filter(function ($employeeProject) {
            $today = Carbon::today();
            return $employeeProject->project->startDate <= $today  && ($employeeProject->project->endDate ?? null) >= $today;
        });

        return $filteredProjects;
    }

    /**
     * Retrieve information about projects.
     *
     * @return array
     */
    public function projectInfo()
    {
        $projects = Project::all();
        if ($projects->isEmpty()) {
            throw new NotFoundResourceException('No records found');
        }
        $upcomingProjects = $projects->filter(function ($project) {
            return $project->startDate > now();
        });
        $activeProjects = $projects->filter(function ($project) {
            $startDate = Carbon::parse($project->startDate);
            $endDate = Carbon::parse($project->endDate);
            $today = Carbon::now();

            return $today->between($startDate, $endDate);
        });

        $statistics = $this->calculateStatistics();

        return [
            'upcomingProjects' => $upcomingProjects,
            'activeProjects' => $activeProjects,
            'projectStatistics' => $statistics['projectMonthlyCounts'],
            'projectOverView' => [
                'type' =>  $statistics['type'],
                'percentage' => $statistics['percentage'],
            ],
            'clientStatistics' => $statistics['clientMonthlyCounts'],
        ];
    }
    /**
     * return project statistics
     */
    private function calculateStatistics()
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $percentageCurrentMonth = 0;
        $percentagePreviousMonth = 0;
        $type = "";

        $allProjectsWIthrashed = Project::withTrashed()->get();
        $projectMonthlyCounts = $allProjectsWIthrashed->groupBy(function ($project) {
            return Carbon::parse($project->created_at)->format('Y');
        })->sortBy(function ($year) {
            return $year;
        })->map(function ($allProjectsWIthrashed) {
            return $allProjectsWIthrashed->groupBy(function ($project) {
                return Carbon::parse($project->created_at)->format('Y-m');
            })->map(function ($projects) {
                return $projects->count();
            })->sortKeys();
        });

        $allClientsWithTrashed = Client::withTrashed()->get();

        $clientMonthlyCounts = $allClientsWithTrashed->groupBy(function ($client) {
            return Carbon::parse($client->created_at)->format('Y');
        })->sortBy(function ($year) {
            return $year;
        })->map(function ($allClientsWithTrashed) {
            return $allClientsWithTrashed->groupBy(function ($client) {
                return Carbon::parse($client->created_at)->format('Y-m');
            })->map(function ($clients) {
                return $clients->count();
            })->sortKeys();
        });

        $currentMonthsCounts = $allProjectsWIthrashed->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
        $previousMonthsCounts = $allProjectsWIthrashed->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $totalProjectCount = $allProjectsWIthrashed->count();
        if ($totalProjectCount > 0) {
            $percentageCurrentMonth = ($currentMonthsCounts / $totalProjectCount) * 100;
            $percentagePreviousMonth = ($previousMonthsCounts / $totalProjectCount) * 100;
        }
        if ($currentMonthsCounts > $previousMonthsCounts) {
            $type = "Increased";
        } else if ($currentMonthsCounts == $previousMonthsCounts) {
            $type = "Stable";
        } else {
            $type = "Decreased";
        }
        return [
            'projectMonthlyCounts' => $projectMonthlyCounts,
            'clientMonthlyCounts' => $clientMonthlyCounts,
            'type' => $type,
            'percentage' => round(abs($percentageCurrentMonth - $percentagePreviousMonth), 1),
        ];
    }


    /**
     * Save a new Project with an array of data.
     * @param array $projectData
     * @return Project|null
     * @throws ModelNotFoundException
     */
    public function save(array $projectData)
    {
        try {
            DB::beginTransaction();

            $columnsToMatch = [
                'name' => $projectData['name'],
                'projectType' => $projectData['projectType'],
                'startDate' => $projectData['startDate'],
                'endDate' => $projectData['endDate'],
                'client_id' => $projectData['client_id']
            ];

            $project = Project::firstOrCreate($columnsToMatch, $projectData);

            DB::commit();

            return $project;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new ModelNotFoundException();
        }
    }


    /**
     * Save a Project requirement with an array of data.
     * @param array $requirementData
     * @return Project|null
     * @throws ModelNotFoundException
     */
    public function saveProjectRequirement($requirementData)
    {
        try {
            DB::beginTransaction();

            foreach ($requirementData as $skillData) {
                ProjectRequirement::firstOrCreate($skillData);
            }

            DB::commit();

            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            throw new ModelNotFoundException();
        }
    }

    /**
     * update a Project requirement with an array of data.
     * @param array $requirementData
     * @return Project|null
     * @throws ModelNotFoundException
     */
    public function updateProjectRequirement($requirementData)
    {
        try {
            DB::beginTransaction();

            $create = [];
            foreach ($requirementData['skills'] as $skillData) {
                if (ProjectRequirement::where('skill', $skillData['skill'])
                    ->where('projectId', $skillData['projectId'])
                    ->exists()
                ) {
                    ProjectRequirement::where('skill', $skillData['skill'])
                        ->where('projectId', $skillData['projectId'])
                        ->delete();
                } else {
                    $create[] = $skillData;
                }
            }

            if (isset($requirementData['new']) && !empty($requirementData['new'])) {
                $this->saveProjectRequirement($requirementData['new']);
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }




    /**
     * Fetch all projects based on a query.
     *
     * @param mixed $query
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch($query)
    {
        try {
            $searchQuery = strtoupper($query);

            if ($searchQuery) {
                $project = Project::where('name', 'ILIKE', "%$searchQuery%")
                    ->orWhere('projectType', 'ILIKE', "%$searchQuery%")
                    ->with('projectHistories.project')
                    ->orderBy('name', 'asc')->get();
                return $project;
            }

            $project = Project::with('projectHistories.project')->orderBy('name', 'asc')->get();


            return $project;
        } catch (Exception $e) {

            return null;
        }
    }


    /**
     * Delete a project by its ProjectId.
     *
     * @param mixed $projectId
     * @return bool
     */
    public function deleteByProjectId($projectId): bool
    {
        try {
            DB::beginTransaction();
            EmployeeProject::where('project_id', Project::where('projectId', $projectId)->value('id'))->delete();
            $project = Project::where('projectId', $projectId)->first();
            ProjectHistory::where('projectId', $project->id)->delete();
            $project->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Find a project by its ProjectId.
     *
     * @param mixed $projectId
     * @return mixed
     */
    public function findByProjectId($projectId)
    {
        try {
            $project = Project::where('projectId', $projectId)->with('employeeProjects.employee')->first();
            return $project;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find a project by its Project Authorization Id.
     *
     * @param mixed $project_id
     * @return mixed
     */
    public function findByAuthId($project_id)
    {
        try {
            $project = EmployeeProject::where('project_id', $project_id)->first();
            return $project;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find a project by its name.
     *
     * @param mixed $name
     * @return mixed
     */
    public function findByProjectName($name)
    {
        try {
            $project = Project::whereRaw('LOWER(name) = ?', strtolower($name))->first();
            return $project;
        } catch (Exception $e) {
            // Log or handle the exception as needed
            return null;
        }
    }


    /**
     * Update a project by its ProjectId.
     *
     * @param array $projectData
     * @return mixed
     */
    public function updateByProjectId(array $projectData)
    {
        try {

            DB::beginTransaction();
            $projectId = $projectData['projectId'];
            // Remove 'projectId' before update
            unset($projectData['projectId']);
            // Update the Project instance
            $updatedRows = Project::where('projectId', $projectId)->update($projectData);
            if ($updatedRows === 0) {
                // No rows were updated, indicating the project was not found
                DB::rollBack();
                return false;
            }
            // Retrieve the updated project
            $project = Project::where('projectId', $projectId)->with('employeeProjects.employee')->first();
            DB::commit();
            return $project;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Assign a project to multiple users.
     *
     * @param array $refIds
     * @param string $projectId
     * @param int $workHours
     * @return mixed
     */
    public function assign(array $refIds, string $projectId, $workHours)
    {
        try {
            DB::beginTransaction();
            $project = Project::where('projectId', $projectId)->first();

            $checkResult = $this->checkExistingAssignments($refIds, $project);

            if (!$checkResult['success']) {
                return $checkResult;
            }

            $checkHours = $this->checkExceedingWorkHours($refIds, $workHours);

            if (!$checkHours['success']) {
                return $checkHours;
            }

            $createResult = $this->createAssignments($refIds, $project, $workHours);

            if (!$createResult['success']) {
                DB::rollBack();
                return $createResult;
            }

            DB::commit();
            return ['success' => true, 'user' => $createResult['user']];
        } catch (Exception $e) {

            DB::rollBack();
            return ['success' => false, 'type' => 'Exception'];
        }
    }

    private function checkExceedingWorkHours($refIds, $workHours)
    {
        $exceededUsers = [];

        foreach ($refIds as $refId) {
            $fixedHour = 8;
            $employee = Employee::where('refId', $refId)->first();

            $totalWorkHours = $employee->employeeProjects()->where('employee_id', $employee->id)->sum('workHours') + (int)$workHours;

            if ($totalWorkHours > $fixedHour) {
                $hoursLeft =  $fixedHour - (int)$employee->employeeProjects()->where('employee_id', $employee->id)->sum('workHours');
                $exceededUsers[] = ucwords($employee->firstName . ' ' . $employee->lastName) . " ( " . $hoursLeft . "hr left )";
            }
        }

        if (!empty($exceededUsers)) {
            return ['success' => false, 'type' => 'exceedingHours', 'user' => $exceededUsers];
        }

        return ['success' => true];
    }

    private function checkExistingAssignments($refIds, $project)
    {
        $employeeIds = Employee::whereIn('refId', $refIds)->pluck('id')->toArray();

        if (count($employeeIds) !== count($refIds)) {
            return ['success' => false, 'type' => 'NotFoundException'];
        }

        $existingAssignments = EmployeeProject::where('project_id', $project->id)
            ->whereIn('employee_id', $employeeIds)
            ->get();

        if ($existingAssignments->isNotEmpty()) {
            $assignedUserNames = $existingAssignments->map(function ($assignment) {
                return ucwords("{$assignment->employee->firstName} {$assignment->employee->lastName}");
            })->toArray();

            return ['success' => false, 'type' => 'existingAssignment', 'user' => $assignedUserNames];
        }

        return ['success' => true];
    }



    private function createAssignments($refIds, $project, $workHours)
    {
        $employeeIds = Employee::whereIn('refId', $refIds)->pluck('id')->toArray();

        if (count($employeeIds) !== count($refIds)) {
            return ['success' => false, 'type' => 'NotFoundException'];
        }

        $createdAssignments = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::findOrFail($employeeId);

            $employee->employeeProjects()->firstOrCreate([
                'project_id' => $project->id,
                'workHours' => (int)$workHours,
            ]);

            $createdAssignments[] = ucwords("{$employee->firstName} {$employee->lastName}");
        }

        return ['success' => true, 'user' => $createdAssignments];
    }



    /**
     * Unassign a project from multiple users.
     *
     * @param array $refIds
     * @param string $projectId
     * @return mixed
     */
    public function unAssign(array $refIds, string $projectId)
    {
        try {
            DB::beginTransaction();
            $assignedUserSuccess = array();
            $notExistingUsers = array();
            foreach ($refIds as $refId) {
                $employee = Employee::where('refId', $refId)->first();
                $project = Project::where('projectId', $projectId)->first();
                if ($employee && $project) { // Check if records are found
                    $existingAssignment = EmployeeProject::where('project_id', '=', $project->id)
                        ->where('employee_id', '=', $employee->id)
                        ->first();
                    if ($existingAssignment) {
                        EmployeeProject::where('project_id', '=', $project->id)
                            ->where('employee_id', '=', $employee->id)
                            ->delete();
                        array_push($assignedUserSuccess, ucwords($employee->firstName . ' ' . $employee->lastName));
                    } else {
                        DB::rollBack();
                        // Assignment doesn't exist for the user in the specified project
                        array_push($notExistingUsers, ucwords($employee->firstName . ' ' . $employee->lastName));
                        return ['success' => false, 'type' => 'notExist', 'user' => $notExistingUsers];
                    }
                } else {
                    // Either employee or project not found
                    return ['success' => false, 'type' => 'NotFoundException'];
                }
            }
            DB::commit();
            return ['success' => true, 'user' => $assignedUserSuccess];
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    /**
     * edit a project schedule by id.
     *
     * @param array $projectData
     * @param string $refId
     * @return array
     */
    public function scheduleEdit(array $projectData, string $refId): array
    {
        try {

            DB::beginTransaction();

            $scheduleId = $projectData['scheduleId'];
            $workHours =  $projectData['workHours'];
            $fixedHour = 8;

            $employee = Employee::where('refId', $refId)->firstOrFail();

            $employeeProject = $employee->employeeProjects()->where('id', $scheduleId)->first();
            $oldWorkHours = $employeeProject->workHours;
            $totalWorkHours = (int)$employee->employeeProjects()->sum('workHours');

            if ($totalWorkHours + (int)$workHours > $fixedHour) {
                $errorMessage = "Working hours has exceeded for " . ucwords($employee->firstName . ' ' . $employee->lastName)
                    . " ( " . $fixedHour - $totalWorkHours . "hr left )," . " Please reduce assignment hours.";
                return ['status' => false, 'message' => $errorMessage];
            }

            $employeeProject->where('id', $scheduleId)->update(['workHours' => $workHours]);

            DB::commit();

            return [
                'status' => true,
                'message' => 'Work hours successfully updated.', 'employee' => $employee,
                'oldWorkHours' => $oldWorkHours,
                'project' => $employeeProject->project,
                'oldWorkHours' => $oldWorkHours,
                'project' => $employeeProject->project,
            ];
        } catch (ModelNotFoundException $e) {
            // Handle the case where the employee or project is not found
            DB::rollBack();
            return ['status' => false, 'message' => 'Employee or project not found.'];
        } catch (Exception $e) {

            DB::rollBack();
            return ['status' => false, 'message' => 'Failed to update work hours,Please try again.'];
        }
    }

    /**
     * get all employee projects by auth
     * @return mixed Projects
     */
    public function employeeProject()
    {
        try {

            $user = Employee::where('id', auth()->user()->employee->id)->first();

            return $user;
        } catch (ModelNotFoundException $e) {
            // Throw an exception if model is not found
            throw new ModelNotFoundException();
        }
    }

    /**
     * Check project timelines and send appropriate reminders.
     *
     * This function iterates over user projects, examines project timelines, and sends reminders based on the project duration.
     *
     * Additionally, job scheduling logic for late project reminders is included in comments, uncomment as needed.
     *
     * @return mixed
     */
    public function checkProjectTimeLines()
    {
        try {
            $currentDate = Carbon::now();
            $users = User::whereHas('employee.employeeProjects.project', function ($query) use ($currentDate) {
                $query->whereColumn('employee_projects.employee_id', 'employees.id')
                    ->where('projects.endDate', '>', $currentDate);
            })->with('employee.employeeProjects.project')->get();

            return $users;
        } catch (ModelNotFoundException $e) {
            // Throw an exception if model is not found
            throw new ModelNotFoundException();
        }
    }

    // Archive operations
    /**
     * Fetch all archived projects.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetchArchives()
    {
        try {
            // Fetch all archived projects (including soft-deleted ones)
            $archivedProjects = Project::onlyTrashed()->orderBy('name', 'asc')->get();

            return $archivedProjects;
        } catch (Exception $e) {
            // Handle exceptions and return null
            return null;
        }
    }

    /**
     * Soft delete (archive) a project by projectId.
     *
     * @param int $projectId
     * @return bool
     */
    public function archiveProject($projectId)
    {
        try {

            DB::beginTransaction();

            // Soft delete (archive) a project by projectId
            Project::where('projectId', '=', $projectId)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Restore a soft-deleted (archived) project by projectId.
     *
     * @param int $projectId
     * @return bool
     */
    public function archivesRestore($projectId)
    {
        try {
            DB::beginTransaction();

            // Restore the soft-deleted project
            $project = Project::withTrashed()->where('projectId', $projectId)->first();

            // Restore the soft-deleted related records in EmployeeProject table
            EmployeeProject::where('project_id', $project->id)->restore();

            $project->restore();

            ProjectHistory::where('projectId', $project->id)->restore();

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Permanently delete a soft-deleted (archived) project by projectId.
     *
     * @param int $projectId
     * @return bool
     */
    public function deleteArchive($projectId)
    {
        try {
            DB::beginTransaction();
            // Retrieve the project before force deletion
            $project = Project::withTrashed()->where('projectId', $projectId)->first();

            EmployeeProject::where('project_id', $project->id)->forceDelete();

            ProjectHistory::where('projectId', $project->id)->forceDelete();
            // Permanently delete the project
            $project->forceDelete();

            DB::commit();
            return true;
        } catch (Exception $e) {

            DB::rollBack();
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Search for archived projects by name or project code.
     *
     * @param string $nameOrCode
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function searchByNameOrCode($nameOrCode)
    {
        try {
            // Search for archived projects by name or project code
            $archivedProjects = Project::withTrashed()->where('name', '=', $nameOrCode)
                ->orWhere('projectCode', '=', $nameOrCode)
                ->get();

            return $archivedProjects;
        } catch (Exception $e) {
            // Handle exceptions and return an empty array
            return [];
        }
    }

    /**
     * Find an archived project by its ProjectId.
     *
     * @param mixed $projectId
     * @return mixed
     */
    public function findArchiveByProjectId($projectId)
    {
        try {
            $project = Project::withTrashed()->where('projectId', $projectId)->first();
            return $project;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieves basic reports for the user.
     * These reports include utilization and contribution metrics.
     * Utilization metrics provide insights into the user's time management,
     * while contribution metrics detail the user's involvement in projects.
     *
     * @return mixed  containing utilization and contribution data for the user.
     */

    public function basicUser()
    {
        try {
            $employeeProjects = EmployeeProject::where('employee_id', auth()->user()->employee->id)
                ->with('project.employeeProjects.employee.specializations.specializationInfo')
                ->with('timeTracks')
                ->withTrashed()
                ->get();

            return $employeeProjects;
        } catch (Exception $e) {
            return null;
        }
    }


    /**
     * Retrieves projects reports .
     *
     * @return mixed
     */
    public function projectReports()
    {
        try {

            $project = Project::with([
                'employeeProjects' => function ($query) {
                    $query->withTrashed();
                }
            ])->withTrashed()->get();

            return $project;
        } catch (Exception $e) {

            return null;
        }
    }

    /**
     * Fetch all asigned projects .
     *
     * @param mixed $query
     * @return mixed
     */
    public function utilization()
    {
        try {

            $utilization = Project::with([
                'projectRequirement',
                'employeeProjects' => function ($query) {
                    $query->withTrashed()->with(['employee' => function ($query) {
                        $query->withTrashed()->with('timeTracks');
                    }]);
                }
            ])->withTrashed()->get();

            return $utilization;
        } catch (ModelNotFoundException $e) {

            return null;
        }
    }
}
