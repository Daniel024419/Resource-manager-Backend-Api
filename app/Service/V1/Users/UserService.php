<?php

namespace App\Service\V1\Users;

use Exception;
use Carbon\Carbon;
use App\Enums\Roles;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\UserFetchResource;
use App\Http\Resources\User\UserSearchResource;
use App\Http\Resources\User\ManagerUserResource;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Http\Resources\User\RegisterUserShowResource;
use App\Http\Resources\User\ArchivedUserFetchResource;
use App\Repository\V1\Skills\SkillsInterfaceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Employee\EmployeeInterfaceRepository;
use App\Notifications\V1\Auth\AccountCompletionNotification;
use App\Service\V1\Notification\NotificationInterfaceService;
use App\Repository\V1\Department\DepartmentInterfaceRepository;
use App\Repository\V1\Notification\NotificationInterfaceRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use App\Repository\V1\Specialization\SpecializationInterfaceRepository;

class UserService implements UserInterfaceService
{
    public $userRepository, $employeeRepository,
        $notificationService, $specializationRepository,
        $departmentRepository, $notificationRepository,
        $skillRepository;

    /**
     * Constructor for injecting dependencies into the class.
     *
     * Initializes a new instance of the class with the provided dependencies.
     *
     * @param UserInterfaceRepository $userRepository
     *     Repository for user-related operations.
     * @param EmployeeInterfaceRepository $employeeRepository
     *     Repository for employee-related operations.
     * @param NotificationInterfaceService $notificationService
     *     Service for handling notifications.
     * @param SpecializationInterfaceRepository $specializationRepository
     *     Repository for specialization-related operations.
     * @param DepartmentInterfaceRepository $departmentRepository
     *     Repository for department-related operations.
     * @param NotificationInterfaceRepository $notificationRepository
     *     Repository for notification-related operations.
     * @param SkillsInterfaceRepository $skillRepository
     *     Repository for skill-related operations.
     */
    public function __construct(
        UserInterfaceRepository $userRepository,
        EmployeeInterfaceRepository $employeeRepository,
        NotificationInterfaceService $notificationService,
        SpecializationInterfaceRepository $specializationRepository,
        DepartmentInterfaceRepository $departmentRepository,
        NotificationInterfaceRepository $notificationRepository,
        SkillsInterfaceRepository $skillRepository,
    ) {
        $this->userRepository = $userRepository;
        $this->employeeRepository = $employeeRepository;
        $this->notificationService = $notificationService;
        $this->specializationRepository = $specializationRepository;
        $this->departmentRepository = $departmentRepository;
        $this->notificationRepository = $notificationRepository;
        $this->skillRepository = $skillRepository;
    }

    /**
     * Fetch all managers.
     *
     * @return array An array containing 'users' and 'status'.
     */
    public function fetchAllManagers() : array
    {
        try {
            $users = collect($this->userRepository->fetchAllManagers());

            if ($users->isEmpty()) {
                throw new ModelNotFoundException();
            }

            return [
                'users' => ManagerUserResource::collection($users),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'users' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Fetch all active accounts.
     *
     * @return array
     */
    public function active(): array
    {
        try {
            // Pass the data for query
            $users =  $this->userRepository->active();

            if (empty($users)) {
                throw new ModelNotFoundException();
            }

            // Return the search results without the inner array
            return [
                'users' => UserFetchResource::collection($users),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'users' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Fetch all inactive accounts.
     *
     * @return array
     */
    public function inactive(): array
    {
        try {
            // Pass the data for query
            $users =  $this->userRepository->inactive();

            if (empty($users)) {
                throw new ModelNotFoundException();
            }

            // Return the search results without the inner array
            return [
                'users' => UserFetchResource::collection($users),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'users' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * fetch all users
     *
     * @return array The user record, or a ModelNotFoundException if no user was found
     */

    public function fetchAllUsers($query): array
    {
        try {
            // Pass the data for query
            $users =  $this->userRepository->fetchAllUsers($query);
            
            if (empty($users)) {
                throw new ModelNotFoundException();
            }

            // Return the search results without the inner array
            return [                
                'usersOverView'=>$users['usersOverView'],
                'users' => UserFetchResource::collection($users['users']),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            

            // User not found
            return [
                'users' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {
           
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Save a new user by processing the provided request.
     *
     * @param Request $request - The HTTP request containing user data.
     *
     * @return array - An array containing the result of the operation, including status, message, and data.
     */
    public function save($request): array
    {

        try {
            $email = $request->email;
            $title = 'New Account Creation';
            $role = $request->roles;
            $refId = Str::uuid();
            $authUser = auth()->user();

            $sender = $sender_email = '';

            if ($authUser) {
                $sender = $authUser->employee->firstName . ' ' . $authUser->employee->lastName;
                $sender_email = $authUser->email;
            }

            $role = Roles::getRoleIdByValue($role);

            if($role == null) {
                return [
                    'message' => 'Invalid role, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            $userData = ['email' => $email];
            $user = $this->userRepository->save($userData);

            $employeeData = [
                'userId' => $user['id'],
                'refId' => $refId,
                'roleId' => $role,
                'bookable' => true,
                'addedBy' => $authUser->employee->id,
            ];

            $employee = $this->employeeRepository->save($employeeData);

            // Save employee department, specialization, and skills
            $department = $this->departmentRepository->storeByName(strtolower($request->department), (int)$employee['id']);
            $specialization = $this->specializationRepository->storeByName(strtolower($request->specialization), (int)$employee['id']);

            if ($department && $specialization && !empty($user) && !empty($employee)) {
                $accessToken = $this->userRepository->find($user['id'])->createToken($email, ['*'], now()->addWeek())->plainTextToken;

                $notification = new AccountCompletionNotification($title, $refId, $email, $role, $accessToken, $sender, $sender_email);

                $inviteCheck = $this->notificationService->accountCompletion($user, $notification);

                if ($inviteCheck) {
                    $this->saveNotificationData($authUser, $employee);

                    return [
                        'message' => 'Set up account mail sent successfully.',
                        'user' => new RegisterUserShowResource($user),
                        'accessToken' => $accessToken,
                        'status' => JsonResponse::HTTP_OK,
                    ];
                } else {

                    return [
                        'message' => 'Failed to send account creation invite, please try reinviting them.',
                        'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                    ];
                }
            }

            $this->deleteIncompleteAccounts($user);
            return [
                'message' => 'Account creation was not successful, please try again.',
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
     * Helper method to save notification data for a new user invitation.
     *
     * @param mixed $authUser  - The authenticated user who initiated the invitation.
     * @param array $employee  - The employee data associated with the new user.
     *
     * @return void
     */
    public function saveNotificationData($authUser, $employee): void
    {
        $notificationData = [
            'message' => 'Invited you to join RM.io',
            'by' => $authUser->employee->id,
            'employee_id' => $employee['id'],
        ];

        $this->notificationRepository->save($notificationData);
    }

    /**
     *  Helper method to delete incomplete account
     *
     * @param mixed $email
     *
     * @return void
     */
    public function deleteIncompleteAccounts($user): void
    {
        $this->userRepository->deleteIncomplteAccountByemail($user['email']);
    }

    /**
     * Find a user by email or username
     *
     * @param string $email The email or username of the user to find
     * @return array The user record, or a ModelNotFoundException if no user was found
     */
    function findByEmail(string $email): array
    {

        try {

            $user =  $this->userRepository->findByEmail($email);

            if (empty($user)) {

                throw new ModelNotFoundException();
            }

            return [
                $user,
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {

            return [
                "message" => "No user found,Please Try again with valid a mail and password",
                'access' => 'Rejected',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * Find a user by email or username
     *
     * @param string $email The email or username of the user to find
     * @return mixed The user record, or a ModelNotFoundException if no user was found
     */

    function findByParam(string $search_param): mixed
    {
        try {
            return  $this->userRepository->findByParam($search_param);
        } catch (ModelNotFoundException $e) {

            return new ModelNotFoundException();
        }
    }

    /**
     * Update the password of a user by email
     *
     * @param string $email The email of the user
     * @param string $password The password to be updated
     * @return mixed The updated user record
     */
    public function updatePassword($userId, $password): mixed
    {
        try {

            return $this->userRepository->updatePassword($userId, $password);
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }


    /**
     * Update the password of a user by email
     *
     * @param string $email The email of the user
     * @param string $password The password to be updated
     * @return mixed The updated user record
     */
    public function updatePasswordByEmail(string $email, string $password): mixed
    {
        try {

            return $this->userRepository->updatePassword($email, $password);
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }

    /**
     * Find a user by ID
     *
     * @param string $userId The ID of the user to find
     * @return mixed The user record, or a ModelNotFoundException if no user was found
     */
    function findById(string $userId): mixed
    {
        try {
            //pass the data for query
            return  $this->userRepository->findById($userId);
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }


    /**
     * Search for a user based on the search parameter
     *
     * @param string $safe_search_param The search parameter
     * @return array The search results and status code
     */
    public function search($safe_search_param): array
    {
        try {
            // Perform the search based on the search parameter
            $results = $this->userRepository->findByParam($safe_search_param);

            if (empty($results)) {
                throw new ModelNotFoundException();
            }

            // Return the search results
            return [
                "results" => userSearchResource::collection($results),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Delete a user.
     *
     * @param mixed $request
     * @return array
     */
    public function deleteUser($request): array
    {
        try {

            $cleanData = $request->validated();
            $email = $cleanData['email'];

            $user =  $this->userRepository->findByEmail($email);
            if (empty($user)) {

                throw new ModelNotFoundException();
            }

            // Eagerly delete both tables
            //boolean res
            $deleleRes = $this->userRepository->deleteByemail($email);

            if (!$deleleRes) {
                return [
                    'message' => 'Account deletion was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Account archived successfully',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {

            return [
                "message" => "No user found,Please Try again with valid email",
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Account deletion was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        }
    }

    /**
     * Send a reminder to complete account setup for a user.
     *
     * @param Request $request - The HTTP request containing user data.
     *
     * @return array - An array containing the result of the operation, including status, message, and data.
     */
    public function reInviteUsers($request): array
    {
        try {
            $cleanData = $request->validated();
            $email = $cleanData['email'];
            $title = 'Account Setup Reminder';
            $authUser = '';
            $sender = $sender_email = '';

            if ($creator = $request->user()) {
                $authUser = $this->userRepository->findByEmail($creator['email']);
                $sender = $authUser['employee']['firstName'] . ' ' . $authUser['employee']['lastName'];
                $sender_email = $creator['email'];
            } else {
                $sender = $sender_email = "rm.io@amalitech.com";
            }

            $user =  $this->userRepository->findByEmail($email);

            // Check if the user exists
            if (empty($user)) {
                throw new ModelNotFoundException();
            }

            // Delete old tokens before creating a new one
            $this->deleteOldTokens($request, $email);

            // Create a new access token
            $accessToken = $this->userRepository->find($user['id'])->createToken($email ,['*'], now()->addWeek())->plainTextToken;

            // Construct and send account completion notification
            $notification = new AccountCompletionNotification(
                $title,
                $user['employee']['refId'],
                $email,
                $user['employee']['roleId'],
                $accessToken,
                $sender,
                $sender_email
            );

            $inviteCheck =  $this->notificationService->accountCompletion($user, $notification);

            if ($inviteCheck) {
                // Save notification data
                $this->saveReInviteNotificationData($authUser, $user);

                return [
                    'message' => 'Account setup reminder sent successfully.',
                    'user' => new RegisterUserShowResource($user),
                    'accessToken' => $accessToken,
                    'status' => JsonResponse::HTTP_OK,
                ];
            } else {
                return [
                    'message' => 'Failed to send account setup reminder, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'No user found, please try again with a valid email.',
                'access' => 'Rejected',
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
     * Helper method to delete old tokens for a user.
     *
     * @param Request $request - The HTTP request containing user data.
     * @param string $email - The email address of the user.
     *
     * @return void
     */
    private function deleteOldTokens($request, $email)
    {
        // Delete all tokens associated with the user's email
        $request->user()->tokens()->where('name', $email)->delete();
    }

    /**
     * Helper method to save notification data.
     *
     * @param mixed $authUser - The authenticated user who initiated the reminder.
     * @param array $user - The user data associated with the reminder.
     *
     * @return void
     */
    private function saveReInviteNotificationData($authUser, $user)
    {
        // Construct notification data based on the user who initiated the reminder
        $notificationData = [
            'message' => 'Reminded you to set up your account on RM.io',
            'by' => optional($authUser)['employee']['id'],
            'employee_id' => $user['employee']['id'],
        ];

        // Save the notification data
        $this->notificationRepository->save($notificationData);
    }

    //archive operations
     /**
     * fetch all archived Users
     * @return array
     */
    public function archivesFetch(): array
    {
        try {
            //pass the data for query
            $archivedUsers = $this->userRepository->fetch();

            return [
                'archives' => ArchivedUserFetchResource::collection($archivedUsers),
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
     * unarchive User
     * @param  $request
     * @var $request
     * @return array
     */
    public function archivesRestore($request)
    {
        try {

            $user =  $this->userRepository->searchByEmail($request->email);
            if (empty($user)) {

                throw new ModelNotFoundException();
            }
            //boolean return
            $unArchive = $this->userRepository->restoreArchive($request->email);

            if (!$unArchive) {
                return [
                    'message' => 'User Unarchive was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Archived user restored successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // Handle the exception if the model is not found (though not used in this context)
            return [
                'message' => 'User does not exist in the archive, please try again.',
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
     * delete archived Users
     * @param $request
     * @var $emailRequest $request
     * @return array
     */
    public function archivesDelete($request)
    {
        try {
            //boolean return
            $delete = $this->userRepository->deleteArchive($request->email);

            if (!$delete) {
                return [
                    'message' => 'Archived User deletion was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Archived User deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // Handle the exception if the model is not found (though not used in this context)
            return [
                'message' => 'Archived User deletion was not successful, please try again.',
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
     * search archived Users
     * @param  $request
     * @var $request , $email
     * @return array
     */
    public function archivesSearch($request)
    {
        try {

            $archivedUsers = $this->userRepository->archivesSearch($request->search_param);

            // Return the search results
            return [
                "archives" => ArchivedUserFetchResource::collection(collect($archivedUsers)->unique('name')),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'archives' => [],
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
}