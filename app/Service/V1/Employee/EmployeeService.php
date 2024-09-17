<?php

namespace App\Service\V1\Employee;

use Exception;
use App\Enums\Roles;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Service\V1\Uploads\UploadService;
use App\Repository\V1\Users\UserRepository;
use App\Repository\V1\Skills\SkillsRepository;
use App\Repository\V1\Employee\EmployeeRepository;
use App\Http\Resources\User\BookableUserFetchResource;
use App\Repository\V1\Department\DepartmentRepository;
use App\Http\Resources\User\UpdateUserInfoShowResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Notification\NotificationRepository;
use App\Repository\V1\Specialization\SpecializationRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class EmployeeService implements EmployeeInterfaceService
{
    /**
     * @var EmployeeRepository
     */
    public $employeeRepository;

    /**
     * @var UserRepository
     */
    public $userRepository;

    /**
     * @var SpecializationRepository
     */
    public $specializationRepository;

    /**
     * @var DepartmentRepository
     */
    public $departmentRepository;

    /**
     * @var SkillsRepository
     */
    public $skillsRepository;

    /**
     * @var upload service
     */
    public $uploadService;
    /*
     * @var notificationsRepository
     */

    public $notificationRepository;
    /**
     * EmployeeService constructor.
     *
     * @param EmployeeRepository $employeeRepository
     * @param UserRepository $userRepository
     * @param SpecializationRepository $specializationRepository
     * @param DepartmentRepository $departmentRepository
     * @param SkillsRepository $skillsRepository
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(
        EmployeeRepository $employeeRepository,
        UserRepository $userRepository,
        SpecializationRepository $specializationRepository,
        DepartmentRepository $departmentRepository,
        SkillsRepository $skillsRepository,
        UploadService $uploadService,
        NotificationRepository $notificationRepository,
    ) {
        $this->employeeRepository = $employeeRepository;
        $this->userRepository = $userRepository;
        $this->specializationRepository = $specializationRepository;
        $this->departmentRepository = $departmentRepository;
        $this->skillsRepository = $skillsRepository;
        $this->uploadService = $uploadService;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * fetch all Bookable users
     *
     * @return mixed The user record, or a ModelNotFoundException if no user was found
     */

    public function fetchBookable($query)
    {
        try {
            // Pass the data for query
            $users =  $this->employeeRepository->fetchBookable($query);

            if (empty($users)) {
                throw new ModelNotFoundException();
            }

            // Return the search results without the inner array
            return [
                'users' => BookableUserFetchResource::collection($users),
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
     * Find an employee by email or employee name.
     *
     * @param string $email
     * @return string<int, string>
     */
    public function findByemail(string $email)
    {
        try {
            // Pass the data for query
            return  $this->employeeRepository->findByemail($email);
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }

    /**
     * Find an employee by parameter.
     *
     * @param string $searchParam
     * @return string
     */
    public function findByParam(string $searchParam)
    {
        try {
            // Pass the data for query
            return  $this->employeeRepository->findByParam($searchParam);
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }

    /**
     * Find an employee by refId.
     *
     * @param string $refId
     * @return string
     */
    public function findByRefId(string $refId)
    {
        try {
            // Pass the data for query
            $employee =  $this->employeeRepository->findByRefId($refId);

            return $employee;
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }

    /**
     * Find an employee by authentication ID.
     *
     * @param int $id
     * @return string
     */
    public function findByAuthId(int $id)
    {
        try {
            // Pass the data for query
            $employee =  $this->employeeRepository->findByAuthId($id);

            return $employee;
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }

    /**
     * Setup an existing employee account.
     *
     * @param Request $request
     * @return mixed
     */
    public function accountSetup($request)
    {

        try {

            // Retrieve validated input directly from the request
            $cleanData = $request->validated();

            $employee = $this->getEmployeeFromRequest($request);

            //handle files upload
            $cleanData['profilePicture'] = $this->handleProfilePictureUpload($request);

            // Construct $employeeData as an associative array
            $employeeData = [
                'refId' => $request->user()->employee->refId,
                'firstName' => $cleanData['firstName'],
                'lastName' => $cleanData['lastName'],
                'phoneNumber' => $cleanData['phoneNumber'],
                'timeZone' => $cleanData['timeZone'],
                'location' => $cleanData['location'],
                'profilePicture' => $cleanData['profilePicture'] ?? $employee['profilePicture'],
            ];

            $employeeDataFromDb = $this->employeeRepository->updateByRefId($employeeData);

            // Check if the employee was updated successfully
            if ($employeeDataFromDb) {
                return [
                    "message" => "Account setup successfully updated.",
                    "user" =>[ new UpdateUserInfoShowResource($employeeDataFromDb) ],
                    "status" => JsonResponse::HTTP_OK,
                ];
            } else {

                return [
                    "message" => "Failed to setup user information. Please try again.",
                    "status" => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'User not found, please try again with valid credentials',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid or corrupted file uploaded',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Update an existing employee profile (owner).
     *
     * @param Request $request
     * @return mixed
     */
    public function updateProfile($request)
    {
        try {

            // Retrieve validated input directly from the request
            $cleanData = $request->validated();

            $employee = $this->getEmployeeFromRequest($request);

            //handle files upload
            $cleanData['profilePicture'] = $this->handleProfilePictureUpload($request);

            // Construct $employeeData as an associative array
            $employeeData = [
                'refId' => $request->user()->employee->refId,
                'firstName' => $cleanData['firstName'],
                'lastName' => $cleanData['lastName'],
                'phoneNumber' => $cleanData['phoneNumber'],
                'profilePicture' => $cleanData['profilePicture'] ?? $employee['profilePicture'],
            ];

            //boolean return
            $employeeDataFromDb = $this->employeeRepository->updateByRefId($employeeData);

            // Check if the employee was updated successfully
            if ($employeeDataFromDb) {
                return [
                    "message" => "Account profile updated successfully.",
                    "user" => new UpdateUserInfoShowResource($employeeDataFromDb),
                    "status" => JsonResponse::HTTP_OK,
                ];
            } else {

                return [
                    "message" => "Failed to update user information. Please try again.",
                    "status" => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'User not found, please try again with valid credentials',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {

            Log::info($e);
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid or corrupted file uploaded',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Edit an existing employee profile by (admin).
     *
     * @param Request $request
     * @return mixed
     */

    public function editProfile($request)
    {

        try {

            // Retrieve validated input directly from the request
            $cleanData = $request->validated();
            $existingEmployeeData = $this->employeeRepository->findByRefId($cleanData['refId']);
            $role = $request->roles;
            $role = Roles::getRoleIdByValue($role);

            // Check if employee data exists; if not, throw a ModelNotFoundException
            if (empty($existingEmployeeData)) {
                throw new ModelNotFoundException();
            }

            // Update user's email address associated with the employee
            $user = $this->userRepository->updateEmailByUserId($existingEmployeeData->userId, $cleanData['email']);

            // Construct $employeeData as an associative array
            $employeeData = [
                'refId' => $cleanData['refId'],
                'firstName' => $cleanData['firstName'],
                'lastName' => $cleanData['lastName'],
                'bookable' => $cleanData['bookable'],
                'roleId' => $role,
            ];

            $this->identifyAndSaveChanges($employeeData, $existingEmployeeData, $request);

            //boolean return
            $employeeDataFromDb = $this->employeeRepository->updateByRefId($employeeData);

            //update the employee department /boolean status
            $department = $this->departmentRepository->updateByName(strtolower($cleanData['department']), $existingEmployeeData['id']);

            //update the employee specialisation /boolean status
            $specialization = $this->specializationRepository->updateByName(strtolower($cleanData['specialization']), $existingEmployeeData['id']);

            // Check if the employee was updated successfully
            if ($employeeDataFromDb  && $department && $specialization  && $user) {
                return [
                    "message" => "Account profile edited successfully.",
                    "user" => [ new UpdateUserInfoShowResource($employeeDataFromDb)],
                    "status" => JsonResponse::HTTP_OK,
                ];
            } else {

                return [
                    "message" => "Failed to edit user information. Please try again.",
                    "status" => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'User not found, please try again with valid credentials',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid or corrupted file uploaded',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Identifies changes between old and new employee data and saves notifications for each change.
     *
     * @param array $employeeData The new data of the employee.
     * @param array $oldEmployeeData The old data of the employee.
     * @param mixed $existingEmployeeData The existing data of the employee.
     * @param mixed $request The request object.
     * @return array Details of the changes made.
     */
    private function identifyAndSaveChanges($employeeData, $existingEmployeeData, $request)
    {
        // Identify changes between old and new employee data
        $changes = [];
        $details = [];

        // Store the old employee data for comparison
        $oldEmployeeData = [
            'refId' => $existingEmployeeData->refId,
            'firstName' => $existingEmployeeData->firstName,
            'lastName' => $existingEmployeeData->lastName,
            'bookable' => $existingEmployeeData->bookable,
            'roleId' => $existingEmployeeData->roleId,
        ];

        foreach ($employeeData as $field => $newValue) {
            // Compare the new value with the old value
            if ($oldEmployeeData[$field] !== $newValue && !in_array($field, ['bookable', 'refId'])) {
                $changes[$field] = [
                    'old' => $oldEmployeeData[$field],
                    'new' => $newValue,
                ];
            }
        }

        foreach ($changes as $field => $change) {
            $notificationData = [
                'message' => "Edited your ({$field}) From {$change['old']} to {$change['new']}",
                'by' => $request->user()->employee->id,
                'employee_id' => $existingEmployeeData->id,
            ];
            $this->notificationRepository->save($notificationData);

            $details[] = "Edited ({$field}) from {$change['old']} to {$change['new']}";
        }
    }

    /**
     * Admin update employee profile.
     *
     * @param Request $request
     * @return mixed
     */

    public function adminUpdateProfile($request)
    {
        try {

            // Retrieve validated input directly from the request
            $cleanData = $request->validated();

            $employee = $this->getEmployeeFromRequest($request);

            $user = $this->userRepository->updateEmailByUserId($request->user()->id, $cleanData['email']);

            //handle files upload
            $cleanData['profilePicture'] = $this->handleProfilePictureUpload($request);

            $employeeData = [
                'refId' => $request->user()->employee->refId,
                'firstName' => $cleanData['firstName'],
                'lastName' => $cleanData['lastName'],
                'phoneNumber' => $cleanData['phoneNumber'],
                'profilePicture' => $cleanData['profilePicture'] ?? $employee['profilePicture'],
            ];

            $employeeDataFromDb = $this->employeeRepository->updateByRefId($employeeData);

            if ($employeeDataFromDb  &&  $user) {
                return [
                    "message" => "Account profile edited successfully.",
                    "user" => new UpdateUserInfoShowResource($employeeDataFromDb),
                    "status" => JsonResponse::HTTP_OK,
                ];
            } else {

                return [
                    "message" => "Failed to edit user information. Please try again.",
                    "status" => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'User not found, please try again with valid credentials',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (BadRequestException $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid or corrupted file uploaded',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Handle the upload of a profile picture from the given request.
     * @param Illuminate\Http\Request $request  The HTTP request object containing the uploaded file.
     * @param array $employee  The associative array representing employee data.
     *
     * @return string  The updated 'profilePicture' URL, either the newly uploaded URL or the existing value on failure.
     */
    private function handleProfilePictureUpload($request)
    {
        if ($request->hasFile('profilePicture') && $request->file('profilePicture')->isValid()) {
            // Attempt to store the file
            $newUrl = $this->uploadService->store($request->file('profilePicture'), "images/profile");

            return $newUrl ?? null;
        } elseif ($request->filled('profilePicture') && preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $request->input('profilePicture'))) {
            $base64String = $request->input('profilePicture');
            $base64Image = substr($base64String, strpos($base64String, ',') + 1);

            $relativePath = "public/images/profile/" . uniqid() . '.png';

            $newUrl = $this->uploadService->storeBase64($relativePath, $base64Image);

            return $newUrl ?? null;

        }
    }

    /**
     * Get employee data based on validated request input and authenticated user.
     *
     * @param Illuminate\Http\Request $request  The validated HTTP request containing user data.
     *
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException If the employee is not found.
     *
     * @return array  The employee data.
     */
    private function getEmployeeFromRequest($request)
    {
        // Find the employee based on the user ID from the authenticated user's information
        $employee = $this->employeeRepository->findByRefId($request->user()->employee->refId);

        if (empty($employee)) {
            throw new ModelNotFoundException();
        }

        return $employee;
    }
   
}

