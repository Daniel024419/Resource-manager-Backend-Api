<?php

namespace App\Service\V1\Project;

use Exception;
use Carbon\Carbon;
use App\Enums\ProjectType;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Http\Resources\Project\FetchProejcts;
use App\Repository\V1\Client\ClientInterfaceRepository;
use App\Repository\V1\Project\ProjectInterfaceRepository;
use App\Http\Resources\Project\FetchUserProjects;
use App\Repository\V1\Employee\EmployeeInterfaceRepository;
use App\Http\Resources\Project\ProjectInfoResource;
use App\Jobs\V1\projects\ProjectDeadlineReminderJob;
use App\Service\V1\Notification\NotificationInterfaceService;
use App\Http\Resources\Project\FetchProjectsExtensions;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Notification\NotificationInterfaceRepository;
use App\Http\Resources\Project\UserAssignedProjectResources;
use App\Notifications\V1\Projects\ProjectRemovalNotification;
use App\Notifications\V1\Projects\NewProjectReminderNotification;
use App\Notifications\V1\Projects\ProjectUpdateReminderNotification;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ProjectService implements ProjectInterfaceService
{
    protected $projectRepository,  $notificationService,
        $clientRepository, $notificationRepository, $userRepository, $EmployeeRepository;
    /**
     * ProjectService constructor.
     *
     * Initializes a new instance of the ProjectService class.
     *
     * @param ProjectInterfaceRepository $projectRepository
     *     An instance of ProjectRepository, providing access to project-related data.
     * @param ClientInterfaceRepository $clientRepository
     *     An instance of ClientRepository, providing access to client-related data.
     * @param NotificationInterfaceRepository $notificationRepository
     *     An instance of NotificationRepository, providing access to notification-related data.
     * @param NotificationInterfaceService $notificationService
     *     An instance of NotificationService, handling notification-related functionality.
     * @param UserInterfaceRepository $userRepository
     *     An instance of UserRepository, providing access to user-related data.
     * @param EmployeeInterfaceRepository $EmployeeRepository
     *     An instance of EmployeeRepository, providing access to employee-related data.
     */
    public function __construct(
        ProjectInterfaceRepository $projectRepository,
        ClientInterfaceRepository $clientRepository,
        NotificationInterfaceRepository $notificationRepository,
        NotificationInterfaceService $notificationService,
        UserInterfaceRepository $userRepository,
        EmployeeInterfaceRepository $EmployeeRepository,

    ) {
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
        $this->notificationService = $notificationService;
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
        $this->EmployeeRepository = $EmployeeRepository;
    }
    /**
     * get all project extentions
     * @param $query
     * @return mixed Projects
     */
    public function extensions($query)
    {
        try {
            //pass the data for query
            $history = $this->projectRepository->fetch($query);

            return [
                'history' => FetchProjectsExtensions::collection($history),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
    /**
     * extend project time line
     *
     * @param Request $request The request object
     * @return array The result of extending the already existing project
     */
    public function extendTimeLine($request)
    {
        $cleanData = $request->validated();
        try {
            $project = $this->projectRepository->findByProjectId($cleanData['projectId']);
            $details = [];

            if ($cleanData['newDate'] <= $project->startDate || $cleanData['newDate'] < $project->endDate) {
                return [
                    'message' => "The new date must be within the project duration ({$project->endDate})",
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            $projectData = [
                'refId' => Str::uuid(),
                'projectId' => $project->id,
                'reason' => $cleanData['reason'],
                'newDate' => $cleanData['newDate'],
                'oldDate' => $project->endDate,
                'createdBy' => $request->user()->employee->id,
            ];

            $projectUpdate = $this->projectRepository->updateByProjectId([
                'projectId' => $cleanData['projectId'],
                'endDate' =>  $cleanData['newDate'],
            ]);

            $endDate = Carbon::parse($project->endDate)->format('M j, Y');
            $newDate = Carbon::parse($cleanData['newDate'])->format('M j, Y');

            if ($this->projectRepository->extendTimeLine($projectData) && $projectUpdate) {
                $details[] = "Updated {$project->name} project end date from {$endDate} to {$newDate}";

                foreach ($project['employeeProjects'] as $employee) {
                    $user = $this->userRepository->findById($employee['employee']['id']);
                    $notificationData = [
                        'message' => "Updated {$project->name} project end date from {$endDate} to {$newDate}",
                        'by' => $request->user()->employee->id,
                        'employee_id' => $user->employee->id,
                    ];
                    $this->notificationRepository->save($notificationData);
                }

                // Send project update notifications to employees if there are any changes
                foreach ($project['employeeProjects'] as $employee) {
                    $user = $this->userRepository->findById($employee['employee']['userId']);
                    $notification = new ProjectUpdateReminderNotification(
                        $project->name . " Project Updates",
                        ucfirst($employee['employee']['firstName']) . ' ' .  ucfirst($employee['employee']['lastName']),
                        $project->name,
                        $details,
                        $request->user()->employee->firstName . ' ' . $request->user()->employee->lastName,
                    );
                    $this->notificationService->projectAssignment($user, $notification);
                }

                return [
                    'message' => 'Project details updated successfully.',
                    'status' => JsonResponse::HTTP_OK,
                ];
            }

            return [
                'message' => 'Project update was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
    
    /**
     * Retrieve and return the projects assigned to the authenticated user.
     *
     * This function retrieves the assigned projects using the project repository,
     * wraps the projects with a resource collection, and returns them along with
     * a success status code. If an exception occurs during the process,
     * it catches the exception, retrieves the error message, and returns
     * an error response with an internal server error status code.
     *
     * @return array An array containing the assigned projects and the response status
     */
    public function projectsAssigned()
    {
        try {
            $projects = $this->projectRepository->projectsAssigned();

            return [
                'projects' => UserAssignedProjectResources::collection($projects),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
    /**
     * Retrieve project information including total upcoming and active projects.
     *
     * @return array
     */
    public function projectInfo()
    {
        try {
            $projects = $this->projectRepository->projectInfo();
            $upcomingProjectsFormatted = ProjectInfoResource::collection($projects['upcomingProjects']->values());
            return [
                'status' => JsonResponse::HTTP_OK,
                'data' => [
                    'totalActiveProjects' => $projects['activeProjects']->count(),
                    'upcomingProjects' => $upcomingProjectsFormatted,
                    'projectStatistics' => $projects['projectStatistics'],
                    'projectOverView' => $projects['projectOverView'],
                    'clientStatistics' => $projects['clientStatistics'],

                ],
            ];
        } catch (NotFoundResourceException $e) {
            return [
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * @param $request
     * @return mixed Projects
     */
    public function fetch($query)
    {
        try {
            //pass the data for query
            $projects = $this->projectRepository->fetch($query);

            return [
                'projects' => FetchProejcts::collection(collect($projects)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * Save a new project using the provided project data.
     *
     * @param Request $request The request object
     * @return array The result of saving the new project
     */
    public function save($request)
    {
        try {
            $cleanData = $request->all();
            $name = $cleanData['name'];
            $frontLetters = substr($name, 0, 2);
            $backLetters = substr($name, -2);
            $projectCode = rand(100, 9999) . $frontLetters . $backLetters;
            $startDate = Carbon::parse($cleanData['startDate']);
            $endDate = Carbon::parse($cleanData['endDate']);

            $projectData = [
                'name' => $name,
                'projectCode' =>  $projectCode,
                'projectType' => strtolower($cleanData['projectType']),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'billable' => (bool) $cleanData['billable'],
                'details' => $cleanData['details'] ?? null,
                'client_id' => $cleanData['clientId'],
                'createdBy' => auth()->user()->employee->id,
            ];

            $project = $this->projectRepository->save($projectData);

            if (empty($project)) {
                throw new ModelNotFoundException();
            }

            if (!$project->wasRecentlyCreated) {
                return [
                    'message' => 'Project name already exists, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }


            $requirementData = [];

            foreach ($cleanData['skills'] as $requirement) {
                $requirementData[] = [
                    'skill' => strtolower($requirement['name']),
                    'projectId' => $project->id,
                ];
            }

            $this->projectRepository->saveProjectRequirement($requirementData);


            return [
                'message' => 'Project created  successfully.',
                'project' => new FetchProejcts($project),
                'status' => JsonResponse::HTTP_CREATED,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Project creation was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * delete/archive a Project
     * @param $request
     * @return array
     */

    public function delete($request)
    {

        try {
            // Retrieve validated input / pop to to array
            $cleanData = $request->validated();

            $checkProject = $this->projectRepository->findByProjectId($cleanData['projectId']);

            if (!$checkProject) {
                return [
                    'message' => 'Project does not exist, please try again.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            //pass the data to repository
            //boolean return
            $delete = $this->projectRepository->deleteByProjectId($cleanData['projectId']);
            if (!$delete) {
                return [
                    'message' => 'Project archiving was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }
            return [
                'message' => 'Project archived successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Update Project data.
     *
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        try {
            $cleanData = $request->all();

            $name = $cleanData['name'];
            $startDate = Carbon::parse($cleanData['startDate']);
            $sender = $request->user()->employee->firstName . ' ' . $request->user()->employee->lastName;
            $checkProject = $this->projectRepository->findByProjectId($cleanData['projectId']);
            $endDate = Carbon::parse($cleanData['endDate']);

            $projectType = strtolower($cleanData['projectType']);

            $existingProject = $checkProject->toArray();

            $projectData = [
                'projectId' => $cleanData['projectId'],
                'name' => $name,
                'projectType' => $projectType,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'billable' => (bool) $cleanData['billable'],
                'details' => $cleanData['details'] ?? null,
                'client_id' => $cleanData['clientId'],
            ];

            $projects = $this->projectRepository->updateByProjectId($projectData);

            if (isset($cleanData['skills'])) {
                $newSkillsWithProjectId = array_map(function ($item) use ($checkProject) {
                    $item['skill'] = $item['name'];
                    unset($item['name']);
                    return array_merge(['projectId' => $checkProject->id], $item);
                }, $cleanData['skills']);

                $mergedSkills = ['skills' => $newSkillsWithProjectId];
                $this->projectRepository->updateProjectRequirement($mergedSkills);
            }

            if (!$projects) {
                return [
                    'message' => 'Project update was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            // Call the function to handle project updates and send notifications
            $this->handleProjectUpdates(
                $projectData,
                $existingProject,
                $checkProject,
                $projects,
                $request,
                $sender
            );

            return [
                'message' => 'Project details updated successfully.',
                'project' => [new FetchProejcts($projects)],
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {

            Log::info($e);
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Handles project updates and sends notifications.
     *
     * @param array $projectData      The updated project data.
     * @param array $existingProject The existing project data.
     * @param mixed $checkProject     The project object being updated.
     * @param mixed $projects         The project data retrieved.
     * @param mixed $request          The request object containing user information.
     * @param string $sender          The sender information for notifications.
     *
     * @return void
     */
    private function handleProjectUpdates(array $projectData, array $existingProject, $checkProject, $projects, $request, string $sender)
    {
        $changes = [];
        $details = [];

        // Find the changes in project data and generate notification messages
        foreach ($projectData as $field => $value) {
            // Remove sensitive fields
            if ($existingProject[$field] !== $value && !in_array($field, ['billable', 'projectType', 'projectCode', 'projectId', 'client_id'])) {
                $changes[$field] = [
                    'old' => $existingProject[$field],
                    'new' => $value,
                ];
                $details[] = "Changed project({$field}) from {$existingProject[$field]} to {$value}";
            }
        }

        if (!empty($details)) {
            // Save notifications for each changed field
            foreach ($changes as $field => $change) {
                foreach ($checkProject['employeeProjects'] as $employee) {
                    $user = $this->userRepository->findById($employee['employee']['id']);
                    $notificationData = [
                        'message' => "Updated {$checkProject->name} project({$field}) From {$change['old']} to {$change['new']}",
                        'by' => $request->user()->employee->id,
                        'employee_id' => $user->employee->id,
                    ];
                    $this->notificationRepository->save($notificationData);
                }
            }

            // Send project update notifications to employees if there are any changes
            foreach ($projects['employeeProjects'] as $employee) {
                $user = $this->userRepository->findById($employee['employee']['userId']);
                $notification = new ProjectUpdateReminderNotification(
                    $checkProject->name . " Project Updates",
                    ucfirst($employee['employee']['firstName']) . ' ' .  ucfirst($employee['employee']['lastName']),
                    $checkProject->name,
                    $details,
                    $sender,
                );
                $this->notificationService->projectAssignment($user, $notification);
            }
        }
    }

    /**
     * Validates and converts project type to enum value.
     *
     * @param string $projectType
     *
     * @return array Response message and status code.
     */
    private function validateProjectType(string $projectType)
    {

        if ($projectType === 'internal') {
            $cleanData['projectType'] = ProjectType::INTERNAL->value;
        } elseif ($projectType === 'external') {
            $cleanData['projectType'] = ProjectType::EXTERNAL->value;
        } else {
            return [
                'message' => 'Unknown project type, please try again.',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        }

        return $cleanData;
    }


    /**
     * search Project data
     * @param $request
     * @return mixed
     */
    public function search($request): array
    {
        try {
            $cleanData = $request->validated();
            // Perform the search based on the search parameter
            $results = $this->projectRepository->findByProjectName($cleanData['name']);

            if (empty($results)) {
                throw new ModelNotFoundException();
            }

            // Return the search results
            return [
                "results" => new FetchProejcts($results),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Find a project by project ID.
     *
     * @param mixed $request
     *     The request containing the project ID or relevant information.
     *
     * @return mixed
     *     The project information or null if the project is not found.
     */

    public function findByProjectId($request)
    {
        try {
            $cleanData = $request->validated();
            // Perform the search based on the search parameter
            $results = $this->projectRepository->findByProjectId($cleanData['projectId']);

            if (empty($results)) {
                throw new ModelNotFoundException();
            }

            // Return the search results
            return [
                "results" => new FetchProejcts($results),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    //archive opressions
    /**
     * fetch all archived projects
     * @return array
     */
    public function archivesFetch()
    {
        try {

            $archivedProjects = $this->projectRepository->fetchArchives();

            return [
                'archives' => FetchProejcts::collection(collect($archivedProjects)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // Handle the exception if the model is not found (though not used in this context)
            return [
                'archives' => 'No Archives yet, please try check later.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * unarchive project
     * @param  $request
     * @var $request
     * @return array
     */
    public function archivesRestore($request)
    {
        try {
            // Retrieve validated input / pop to to array
            $cleanData = $request->validated();

            $checkArchive = $this->projectRepository->findArchiveByProjectId($cleanData['projectId']);

            if (!$checkArchive) {
                return [
                    'message' => 'Project archive does not exist, please try again.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            //boolean return
            $unArchive = $this->projectRepository->archivesRestore($cleanData['projectId']);

            if (!$unArchive) {
                return [
                    'message' => 'Project Unarchive was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Project archive restored successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // Handle the exception if the model is not found (though not used in this context)
            return [
                'message' => 'Project Unarchive was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * delete archived projects
     * @param $request
     * @var $ProjectIdRequest $request
     * @return array
     */
    public function archivesDelete($request)

    {
        try {
            // Retrieve validated input / pop to to array
            $cleanData = $request->validated();

            $checkArchive = $this->projectRepository->findArchiveByProjectId($cleanData['projectId']);

            if (!$checkArchive) {
                return [
                    'message' => 'Project archive does not exist, please try again.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            //boolean return
            $delete = $this->projectRepository->deleteArchive($cleanData['projectId']);

            if (!$delete) {
                return [
                    'message' => 'Archived project deletion was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Archived project deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // Handle the exception if the model is not found (though not used in this context)
            return [
                'message' => 'Archived project deletion was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * search archived projects
     * @param  $request
     * @var $request , $ProjectId
     * @return array
     */
    public function archiveseSarch($request)
    {
        try {

            $archivedProjects = $this->projectRepository->searchByNameOrCode($request->name);

            // Return the search results
            return [
                "results" =>  FetchProejcts::collection(collect($archivedProjects)->unique('name')),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {

            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * Assigns a project to users.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function assign($request): array
    {
        try {
            $cleanData = $request->validated();
            $projectId = $cleanData['projectId'];
            $workHours = $cleanData['workHours'];
            $refIds = $request->input('refId');

            if (!is_array($refIds)) {
                return [
                    'message' => 'Users are expected to be one or more , Please select at least one',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            $project = $this->projectRepository->findByProjectId($projectId);

            if (!$project) {
                throw new ModelNotFoundException();
            }

            $assignSuccess = $this->projectRepository->assign($refIds, $project->projectId, $workHours);

            if ($assignSuccess['success'] == true) {
                // Call the private function to send notifications
                $this->sendProjectAssignmentNotifications($refIds, $project, $assignSuccess['user']);

                return [
                    'message' => ucwords($project->name) . ' project assigned to ' . implode(', ', $assignSuccess['user']) . ' successfully.',
                    'status' => JsonResponse::HTTP_OK,
                ];
            }

            if ($assignSuccess['success'] === false) {
                // Call the private function to handle assignment errors
                return $this->handleAssignmentError($assignSuccess, $project);
            }
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Sorry, the project name does not match any record, Please try again.',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Sends project assignment notifications to the assigned users.
     *
     * @param array  $refIds        The array of reference IDs of the assigned users.
     * @param mixed  $project       The project object associated with the assignment.
     * @param array  $assignedUsers The array of names of the assigned users.
     * @param mixed  $request       The request object containing user information.
     *
     * @return void
     */
    public function sendProjectAssignmentNotifications(array $refIds, $project, array $assignedUsers)
    {
        $authUser = auth()->user()->employee;
        $title = 'Project Assignment';
        foreach ($refIds as $refId) {
            $employee = $this->EmployeeRepository->findByRefId($refId);
            $user = $this->userRepository->findById($employee['userId']);
            $notificationData = [
                'message' => 'Assigned you to ' . ucwords($project->name) . ' project',
                'by' => $authUser->id,
                'employee_id' => $employee->id,
            ];

            $endDate = Carbon::parse($project->endDate);
            $startDate = Carbon::parse($project->startDate);

            $notification = new NewProjectReminderNotification(
                $title,
                ucfirst($employee->firstName) . ' ' .  ucfirst($employee->lastName),
                $project->name,
                implode(', ', $assignedUsers),
                $project->details,
                $startDate->format('F j, Y g:i A'),
                $endDate->format('F j, Y g:i A'),
                ucfirst($authUser->firstName) . ' ' . ucfirst($authUser->lastName),

            );

            // Send notification and save it
            $this->notificationService->projectAssignment($user, $notification);
            $this->notificationRepository->save($notificationData);
        }
    }

    /**
     * Handles errors that occur during project assignment.
     *
     * @param array $assignSuccess The result of the assignment operation.
     * @param mixed $project       The project object associated with the assignment.
     *
     * @return array Error message and status code.
     */
    private function handleAssignmentError(array $assignSuccess, $project)
    {
        $errorMessage = '';

        switch ($assignSuccess['type']) {
            case 'existingAssignment':
                $errorMessage = implode(', ', $assignSuccess['user']) .
                    (count($assignSuccess['user']) === 1 ? ' already exists' : ' already exist') .
                    ' on this project';
                break;

            case 'exceedingHours':
                $exceededUsers = implode(', ', $assignSuccess['user']);
                $errorMessage = "Working hours has exceeded for $exceededUsers. Please reduce assignment hours.";
                break;

            case 'notAll':
                $errorMessage = implode(', ', $assignSuccess['user']) .
                    (count($assignSuccess['user']) === 1 ? ' was not assigned successfully' :
                        ' were not assigned successfully') . ', please try again';
                break;

            case 'NotFoundException':
                $errorMessage = 'User not found, please check the users and try again';
                break;

            default:
                $errorMessage = 'Project assignment was not successful, please try again.';
                break;
        }

        return [
            'message' => $errorMessage,
            'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
        ];
    }

    /**
     * assigns a project to a user
     * @param $request
     * @return mixed
     */
    public function unAssign($request): array
    {
        try {
            $cleanData = $request->validated();
            $projectId = $cleanData['projectId'];
            $title = 'Project Updates';
            $refIds = $request->input('refId');
            if (!is_array($refIds)) {
                return [
                    'message' => 'Users are expected to be one or more , Please select at least one',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            $project = $this->projectRepository->findByProjectId($projectId);
            if (empty($project)) {
                throw new ModelNotFoundException();
            }

            return $this->handleProjectUnassignment($refIds, $project, $request, $title);
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Sorry, the project name or id does not match any record, try again.',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Handle project unassignment and send notifications.
     *
     * @param array  $refIds   The array of reference IDs of the unassigned users.
     * @param mixed  $project  The project object from which users are being unassigned.
     * @param mixed  $request  The request object containing user information.
     * @param string $title    The title for the notification.
     *
     * @return array Response message and status code.
     */
    private function handleProjectUnassignment(array $refIds, $project, $request, string $title)
    {
        $unassignSuccess = $this->projectRepository->unAssign($refIds, $project->projectId);

        if ($unassignSuccess['success'] == true) {
            $unassignedUsers = [];

            foreach ($refIds as $refId) {
                $employee = $this->EmployeeRepository->findByRefId($refId);
                $user = $this->userRepository->findById($employee['userId']);
                $notificationData = [
                    'message' => 'Removed you from ' . $project->name . ' project',
                    'by' => $request->user()->employee->id,
                    'employee_id' => $employee->id,
                ];

                $notification = new ProjectRemovalNotification(
                    $title,
                    $employee->firstName . ' ' . $employee->lastName,
                    $project->name,
                    $project->details,
                    $request->user()->employee->firstName . ' ' . $request->user()->employee->lastName,
                );

                // Send notification and save it
                $this->notificationService->projectAssignment($user, $notification);
                $this->notificationRepository->save($notificationData);

                $unassignedUsers[] = $employee->firstName . ' ' . $employee->lastName;
            }

            return [
                'message' => implode(', ', $unassignedUsers) .
                    (count($unassignedUsers) === 1 ? ' is' : ' are') . ' no longer on this ' . $project->name . ' project',
                'status' => JsonResponse::HTTP_OK,
            ];
        }

        $errorMessage = '';

        switch ($unassignSuccess['type']) {
            case 'NoExistingAssignment':
                $errorMessage = 'One or more users are not working on this ' . $project->name . ' project';
                break;
            case 'NotFoundException':
                $errorMessage = 'User not found, please check the users and try again';
                break;
            case 'notExist':
                $errorMessage = 'No existing project for ' . implode(', ', $unassignSuccess['user']) . ' , please try again';
                break;
            default:
                $errorMessage = 'Project Unassignment was not successful, please try again.';
                break;
        }

        // Return failed unassignment
        return [
            'message' => $errorMessage,
            'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
        ];
    }

    /**
     * edit employee project schedule .
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function scheduleEdit($request): array
    {
        try {
            $cleanData = $request->validated();

            $projectData = [
                'scheduleId' => $cleanData['scheduleId'],
                'workHours' => (int) $cleanData['workHours']
            ];
            $editResponse = $this->projectRepository->scheduleEdit($projectData, $cleanData['refId']);

            if ($editResponse['status'] == true) {
                $user = $this->userRepository->findById($editResponse['employee']['userId']);
                $notificationData = [
                    'message' => "Updated {$editResponse['project']['name']} project work hours From {$editResponse['oldWorkHours']} to {$cleanData['workHours']}",
                    'by' => $request->user()->employee->id,
                    'employee_id' => $user->employee->id,
                ];
                $this->notificationRepository->save($notificationData);
                $details[] = $notificationData['message'];

                $notification = new ProjectUpdateReminderNotification(
                    $editResponse['project']['name'] . " Project Schedule Updates",
                    $editResponse['employee']['firstName'] . ' ' . $editResponse['employee']['lastName'],
                    $editResponse['project']['name'],
                    $details,
                    $request->user()->employee->firstName . ' ' . $request->user()->employee->lastName,
                );
                $this->notificationService->projectAssignment($user, $notification);

                return [
                    'message' => $editResponse['message'],
                    'NewWorkHours' => (int) $cleanData['workHours'],
                    'status' => JsonResponse::HTTP_OK,
                ];
            }
            return [
                'message' => $editResponse['message'],
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * get all employee projects by auth
     * @return mixed Projects
     */
    public function employeeProject()
    {
        try {

            //pass the data for query
            $user = $this->projectRepository->employeeProject();

            return [
                'projects' => FetchUserProjects::collection(collect($user->employeeProjects)),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {

            Log::info($e);
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Check project timelines and send appropriate reminders.
     *
     * This function iterates over user projects, examines project timelines, and sends reminders based on the project duration.
     * - For projects lasting a year or more, it sends quarterly reminders.
     * - For projects lasting a month or more, it sends reminders every 3 days.
     * - For projects lasting less than a month, it sends reminders every 3 days and weekly reminders.
     *
     * Additionally, job scheduling logic for late project reminders is included in comments, uncomment as needed.
     *
     * @return array
     */
    public function checkProjectTimeLines($schedule)
    {
        $users = $this->projectRepository->checkProjectTimeLines();
        if (count($users)) {
            foreach ($users as $user) {
                foreach ($user->employee->employeeProjects as $employeeProject) {
                    $project = $employeeProject->project;

                    $endDate = Carbon::parse($project->endDate);
                    $startDate = Carbon::parse($project->startDate);
                    $durationInDays = $endDate->diffInDays($startDate);
                    // Log::info([$project, 'startDate' => $startDate->diffForHumans()]);

                    $title = "";
                    $employeeProject = [$project]; // Initialize the array directly

                    switch (true) {
                        case $durationInDays >= 365:
                            $title = "Monthly Project Reminder";
                            $employeeProject = array();
                            array_push($employeeProject, $project);
                            $schedule->job(new ProjectDeadlineReminderJob(
                                $this->notificationService,
                                $employeeProject,
                                $user,
                                $title
                            ))->monthly();
                            break;

                        case $durationInDays >= 30:
                            $title = "Weekly Project Reminder";
                            $employeeProject = array();
                            array_push($employeeProject, $project);
                            $schedule->job(new ProjectDeadlineReminderJob(
                                $this->notificationService,
                                $employeeProject,
                                $user,
                                $title
                            ))->weekly();
                            break;

                        case $durationInDays >= 2:
                            $title = "Daily Project Reminder";
                            $employeeProject = array();
                            array_push($employeeProject, $project);
                            // Adjust cron frequency based on duration
                            $cronFrequency = ($durationInDays <= 2) ? '0 */12 * * *' : '0 */36 * * *';
                            $schedule->job(new ProjectDeadlineReminderJob(
                                $this->notificationService,
                                $employeeProject,
                                $user,
                                $title
                            ))->cron($cronFrequency);
                            break;

                        case $durationInDays >= 8 && $durationInDays <= 14:
                            $title = "Daily Project Reminder";
                            $employeeProject = array();
                            array_push($employeeProject, $project);
                            $schedule->job(new ProjectDeadlineReminderJob(
                                $this->notificationService,
                                $employeeProject,
                                $user,
                                $title
                            ))->cron('0 */72 * * *');
                            break;

                        case $durationInDays > 14:
                            $title = "Daily Project Reminder";
                            $employeeProject = array();
                            array_push($employeeProject, $project);
                            $schedule->job(new ProjectDeadlineReminderJob(
                                $this->notificationService,
                                $employeeProject,
                                $user,
                                $title
                            ))->weekly();
                            break;
                    }
                }
            }
        }
    }
}
