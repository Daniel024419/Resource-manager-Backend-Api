<?php

namespace App\Service\V1\AutoAssign;

use App\Enums\ProjectDurationType;
use Carbon\Carbon;
use App\Models\V1\Project\Project;
use App\Models\V1\skill\Skill;
use App\Repository\V1\Project\ProjectInterfaceRepository;
use App\Service\V1\Project\ProjectInterfaceService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AutoAssignService implements AutoAssignServiceInterface
{
    public function __construct(public ProjectInterfaceRepository $projectRepository, public ProjectInterfaceService $projectService)
    {
    }

    /**
     * Automatically assigns employees to a project based on provided data.
     *
     * @param  Project  $project
     * @param  array  $data
     * @return array
     */
    public function autoAssign(Project $project, array $data)
    {
        try {


            $duration = $this->calculateProjectDuration($project->startDate, $project->endDate);

            $ratingRange = ProjectDurationType::getRatingRange($duration);

            $data['projectId'] = $project->id;
            $employees = $this->getEmployees($data);

            $assignedEmployees = Array();
            $unassignedSkills = [];

            foreach ($data['skills'] as $skill) {
                $skillName = $skill['skill'];
                $requiredCount = $skill['number'];
                $count = 0;

                $filteredEmployees = array_filter($employees, function ($employee) use ($skillName, $ratingRange, $data) {
                    $skillRating = $employee['skillRating'];
                    return strtolower($employee['skillName']) === strtolower($skillName)
                        && $skillRating >= $ratingRange[0]
                        && $skillRating <= $ratingRange[1];
                });

                $availableCount = count($filteredEmployees);

                if ($availableCount < $requiredCount) {
                    $unassignedSkills[$skillName] = [
                        'required' => $requiredCount,
                        'available' => $availableCount
                    ];
                    continue;
                }

                usort($filteredEmployees, function ($a, $b) {
                    return $a['availability'] <=> $b['availability'];
                });

                foreach ($filteredEmployees as $key => $employee) {
                    if ($count < $requiredCount) {

                        array_push($assignedEmployees,$employee['refId']);

                        $employees[$key]['availability'] -= $project->workHoursPerDay;
                        $count++;

                        if ($count >= $requiredCount) {
                            break;
                        }
                    } else {
                        break;
                    }
                }
            }

            if (!empty($unassignedSkills)) {
                $errorMessage = 'Insufficient employees available for the following skills:';
                foreach ($unassignedSkills as $skillName => $counts) {
                    $errorMessage .= " $skillName - required: {$counts['required']}, available: {$counts['available']};";
                }

                return [
                    "message" => $errorMessage,
                    "status" => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            $response = $this->projectRepository->assign($assignedEmployees, $project->projectId, $data['workingHours']);

            if (!$response['success']) {
                return [
                    "message" => 'Employees auto assign was unsuccessfully. Please try again or try assigning manually.',
                    "status" => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            $this->projectService->sendProjectAssignmentNotifications($assignedEmployees, $project, $response['user']);
            return [
                "message" => 'Employees successfully assigned to the project.',
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
           return new Exception('Auto Assign failed',JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Calculates the duration of the project based on its start and end dates.
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return string
     */
    private function calculateProjectDuration($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $durationInDays = $startDate->diffInDays($endDate);

        if ($durationInDays <= 180) {
            return ProjectDurationType::SHORT->value;
        } else {
            return ProjectDurationType::LONG->value;
        }
    }


    /**
     * Retrieves eligible employees based on project requirements.
     *
     * @param  array  $data
     * @return array
     */
    private function getEmployees(array $data)
    {
        $excludingProjectId = $data['projectId'];
        unset($data['projectId']);

        $skillNames = collect($data['skills'])->pluck('skill')->map('strtolower')->toArray();
        $workingHours = (int)$data['workingHours'];

        $employees = [];
        foreach ($skillNames as $skillName) {
            $skills = Skill::where(DB::raw('LOWER(name)'), strtolower($skillName))->get();

            foreach ($skills as $skill) {
                $firstName = $skill->employee->firstName ?? null;
                $lastName = $skill->employee->lastName ?? null;

                if (!empty($firstName) && !empty($lastName)) {
                    $employeeProjects = $skill->employee->employeeProjects->where('project_id', '!=', $excludingProjectId) ?? collect();
                    $available = 8 - $employeeProjects->pluck('workHours')->sum();
                    $availableAfterWorkingHours = $available - $workingHours;

                    if ($availableAfterWorkingHours >= 0) {
                        $employees[] = [
                            'refId' => $skill->employee->refId,
                            'name' => $firstName . ' ' . $lastName,
                            'skillName' => $skill->name,
                            'skillRating' => $skill->rating,
                            'availability' =>  $availableAfterWorkingHours,
                        ];
                    }
                }
            }
        }

        return $employees;
    }
}