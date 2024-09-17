<?php

namespace App\Service\V1\TimeOff;

use App\Enums\Roles;
use App\Http\Resources\TimeOff\BookLeaveResource;
use App\Http\Resources\TimeOff\CreateTimeOffTypeResource;
use App\Http\Resources\TimeOff\PeopleOnLeaveFetchResource;
use App\Http\Resources\TimeOff\PeopleOnPendingFetchResource;
use App\Http\Resources\TimeOff\TimeOffInfoResource;
use App\Http\Resources\TimeOff\TimeOffRequestResource;
use App\Http\Resources\TimeOff\TimeOffTypeFetchResource;
use App\Http\Resources\TimeOff\UpdateTimeOffTypeResource;
use App\Http\Resources\TimeOff\UserHistoryFetchResource;
use App\Notifications\V1\TimeOff\TimeOffNotification;
use App\Repository\V1\Notification\NotificationInterfaceRepository;
use App\Repository\V1\TimeOff\TimeOffRepositoryInterface;
use App\Service\V1\Notification\NotificationService;
use App\Service\V1\Uploads\UploadInterfaceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class TimeOffService implements TimeOffServiceInterface
{

    public function __construct(
        public TimeOffRepositoryInterface $timeOffRepository,
        public NotificationInterfaceRepository $notificationRepository,
        public NotificationService $notificationService,
        public UploadInterfaceService $uploadService
    ) {
    }

    /**
     * Reassign a leave request
     *
     *
     * @param array $data
     * @return array
     */
    public function reassignLeaveRequest(array $data)
    {
        try {


            $isMoreThanOne = (count($data['leaveRequests']) > 1);

            $reassigned = $this->timeOffRepository->reassignLeaveRequest($data);

            if (!$reassigned) {
                throw new Exception('Failed to reassign ' . count($data['leaveRequests']) . ' leave ' . ($isMoreThanOne ? 'requests' : 'request.'));
            }

            $this->sendInAppNotification(
                count($data['leaveRequests']) . ' leave ' . ($isMoreThanOne ? 'requests' : 'request') . ' have been reassigned to you to manage',
                auth()->user()->employee->id,
                $data['userId']
            );

            return [
                'message' => 'Leave ' . ($isMoreThanOne ? 'requests' : 'request') . ' successfully reassigned.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Internal server error. Please try again later.',
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }

    }


    /**
     * Retrieve a users upcoming timeOffs, employees currently on timeoff and employees returning from time off.
     *
     * @return mixed
     */
    public function timeOffInfo()
    {
        try {
            $timeOffInfo = $this->timeOffRepository->timeOffInfo();

            $upcomingTimeOffsFormatted = TimeOffInfoResource::collection($timeOffInfo['upcomingTimeOffs']->values());
            $employeesOnTimeOffFormatted = TimeOffInfoResource::collection($timeOffInfo['employeesOnTimeOff']->values());
            $employeesReturningFromTimeOffFormatted = TimeOffInfoResource::collection($timeOffInfo['employeesReturningFromTimeOff']->values());
            return [
                'message' => 'Fetched users upcoming timeOffs, employees currently on timeoff and employees returning from time off successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => [
                    'totalNumberOfEmployeesOnLeave' => $employeesOnTimeOffFormatted->count(),
                    'employeesOnTimeOffOverView' => $timeOffInfo['employeesOnTimeOffOverView'],
                    'upcomingTimeOffs' => $upcomingTimeOffsFormatted,
                    'employeesOnTimeOff' => $employeesOnTimeOffFormatted,
                    'employeesReturningFromTimeOff' => $employeesReturningFromTimeOffFormatted,
                    'upcomingHolidays' => $timeOffInfo['upcomingHolidays'],
                ]
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
     * Fetch time off requests and format the response.
     *
     * @return array Response containing time off requests.
     */
    public function timeOffRequest()
    {
        try {
            $timeOffInfo = $this->timeOffRepository->timeOffRequest();

            $allTimeOffRequestsFormatted = TimeOffRequestResource::collection($timeOffInfo['allTimeOffRequests']->values());
            $rejectedTimeOffRequestsFormatted = TimeOffRequestResource::collection($timeOffInfo['rejectedTimeOffRequests']->values());
            $approvedTimeOffRequestsFormatted = TimeOffRequestResource::collection($timeOffInfo['approvedTimeOffRequests']->values());
            return [
                'message' => 'Fetched time offs successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => [
                    'allTimeOffRequests' => $allTimeOffRequestsFormatted,
                    'rejectedTimeOffRequests' => $rejectedTimeOffRequestsFormatted,
                    'approvedTimeOffRequests' => $approvedTimeOffRequestsFormatted
                ]
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
     * Book leave for an employee.
     *
     * @param array $data
     * @return array
     */
    public function bookLeave(array $data)
    {
        try {
            if (auth()->user()->employee->roleId === Roles::getRoleIdByValue(Roles::ADMIN->value)) {
                return ['message' => 'Only Basic user and Manager can book leaves.', 'status' => JsonResponse::HTTP_FORBIDDEN];
            }

            $leaveTypeName = $data['leaveTypeName'];
            $data['type'] = $data['leaveTypeId'];
            $data['userId'] = auth()->user()->id;
            $requiresProof = $data["requiresProve"];

            unset($data["leaveType"], $data["leaveTypeId"], $data["leaveTypeName"], $data["requiresProve"]);

            if ($requiresProof) {
                if ($data['proof']) {
                    $newUrl = $this->uploadService->store($data['proof'], "leave/proof");
                    $data['proof'] = $newUrl ?? null;
                }
            } else {
                unset($data['proof']);
            }

            if ($this->timeOffRepository->hasUsedUpAnnualLeave($data['type'], $data['userId'])) {
                return ['message' => 'You have used up your annual leave allocation.', 'status' => JsonResponse::HTTP_FORBIDDEN];
            }

            $newLeave = $this->timeOffRepository->bookLeave($data);

            if (!$newLeave) {
                return [
                    'message' => 'Failed to book ' . ucwords($leaveTypeName),
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }

            if (!$newLeave->wasRecentlyCreated) {
                return [
                    'message' => 'You have already booked ' . ucwords($leaveTypeName) . ' from ' . Carbon::parse($newLeave['startDate'])->format('F j, Y') . ' to ' . Carbon::parse($newLeave['endDate'])->format('F j, Y') . '.',
                    'status' => JsonResponse::HTTP_CONFLICT,
                ];
            }

            $this->sendInAppNotification("Your request for " . ucwords($newLeave['typeDetail']['name']) . " has been booked. We will evaluate it and get back to you.", auth()->user()->employee->id, auth()->user()->employee->id);

            $this->sendInAppNotification(ucwords(auth()->user()->employee->firstName . ' ' . auth()->user()->employee->lastName) . " has booked " . ucwords($newLeave['typeDetail']['name']), auth()->user()->employee->id, auth()->user()->employee->addedBy);

            return [
                'message' => 'Leave booked successfully.',
                'status' => JsonResponse::HTTP_CREATED,
                'data' => new BookLeaveResource($newLeave),
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
     * Retrieve all leave types.
     *
     * @return array
     */
    public function leaveTypes()
    {
        try {
            $leavetypes = $this->timeOffRepository->leaveTypes();

            return [
                'message' => 'Fetched all leave type successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => TimeOffTypeFetchResource::collection($leavetypes),
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
     * Retrieve pending leaves.
     *
     * @return array
     */
    public function pendingLeaves()
    {
        try {
            $peopleOnPending = $this->timeOffRepository->pendingLeaves();

            return [
                'message' => 'Fetched all pending leave requests successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => PeopleOnPendingFetchResource::collection($peopleOnPending),
            ];
        } catch (NotFoundResourceException $e) {
            return [
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            Log::error($e);

            return [
                'error' => 'Internal server error.',
                'message' => 'Something went wrong. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieve pending leaves.
     *
     * @return array
     */
    public function leaveHistory()
    {
        try {
            $history = $this->timeOffRepository->leaveHistory();

            return [
                'message' => 'Fetched all user leave history successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => UserHistoryFetchResource::collection($history),
            ];
        } catch (NotFoundResourceException $e) {
            return [
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Internal server error.',
                'message' => 'Something went wrong. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Retrieve information about employees on leave.
     *
     * @return array
     */
    public function employeesOnLeave()
    {
        try {
            $peopleOnLeave = $this->timeOffRepository->employeesOnLeave();

            if (empty($peopleOnLeave)) {
                return [
                    'message' => 'No one is on leave.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            return [
                'message' => 'People on leave fetched successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => PeopleOnLeaveFetchResource::collection($peopleOnLeave),
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Internal server error.',
                'message' => 'Something went wrong. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Manage the approval or rejection of a leave request.
     *
     * @param string $action The action to perform (approve or reject).
     * @param string $refId The reference ID of the leave request.
     * @return array Response data containing the status and message.
     */
    public function manageLeave(string $action, string $refId)
    {
        try {
            $validActions = ['approve', 'reject'];

            if (!in_array($action, $validActions)) {
                return [
                    'message' => 'Invalid action',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }

            $action = ($action === 'approve') ? 'approved' : 'rejected';

            $leaveRequest = $this->timeOffRepository->manageLeave($action, $refId);

            $typeName = ucwords($leaveRequest['typeDetail']['name']);
            $message = "Your request for $typeName has been $action.";

            $userId = auth()->user()->employee->id;
            $leaveUserId = $leaveRequest['user']['employee']['id'];

            $this->sendInAppNotification($message, $userId, $leaveUserId);
            $this->sendEmail('Leave ' . ucfirst($action), $message, $leaveRequest['user']);

            return [
                'message' => "Leave request $action successfully.",
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (AuthenticationException $e) {
            return [
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_UNAUTHORIZED,
            ];
        } catch (NotFoundResourceException $e) {
            return [
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => 'Internal server error.',
                'message' => 'Something went wrong. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }



    /**
     * Create a new leave type.
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        try {
            $newLeave = $this->timeOffRepository->create($data);

            if (!$newLeave) {
                return [
                    'message' => 'Failed to create new leave type.',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
            return [
                'message' => 'New leave type created successfully.',
                'status' => JsonResponse::HTTP_CREATED,
                'data' => new CreateTimeOffTypeResource($newLeave),
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
     * Update details of a leave type.
     *
     * @param array $data
     * @param string $refId
     * @return array
     */
    public function update(array $data, string $refId)
    {
        try {
            $newLeave = $this->timeOffRepository->update($data, $refId);

            if (!$newLeave) {
                return [
                    'message' => 'Failed to update leave type.',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
            return [
                'message' => 'Leave type updated successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => new UpdateTimeOffTypeResource($newLeave),
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
     * Find a leave type by its reference ID.
     *
     * @param string $refId
     * @return array
     */
    public function findLeaveTypeByRefId(string $refId)
    {
        try {
            $LeaveType = $this->timeOffRepository->findLeaveTypeByRefId($refId);
            if (!$LeaveType) {
                return [
                    'message' => 'Failed to find leave type.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }
            return [
                'message' => 'Leave type found successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => $LeaveType,
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
     * Delete a leave type by its ID.
     *
     * @param string $refId
     * @return array
     */
    public function deleteLeaveType(string $refId)
    {
        try {
            $LeaveType = $this->timeOffRepository->deleteLeaveType($refId);
            if (!$LeaveType) {
                return [
                    'message' => 'Failed to delete leave type.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }
            return [
                'message' => 'Leave type deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
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
     * Send an in-app notification.
     *
     * @param string $message
     * @param int $by
     * @param int $employeeId
     * @return void
     */
    private function sendInAppNotification($message, $by, $employeeId)
    {
        try {
            $notificationData = [
                'message' => $message,
                'by' => $by,
                'employee_id' => $employeeId,
            ];
            $this->notificationRepository->save($notificationData);
        } catch (Exception $e) {
            Log::error('Error sending in-app notification: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification.
     *
     * @param string $title The title of the email notification.
     * @param string $message The message content of the email notification.
     * @param array $user The user data including email address.
     * @return void
     */
    private function sendEmail($title, $message, $user)
    {
        try {
            $notification = new TimeOffNotification(
                $title,
                $message,
                $user['email'],
            );
            $this->notificationService->timeOff($user, $notification);
        } catch (Exception $e) {
            Log::error('Error sending email notification: ' . $e->getMessage());
        }
    }
}
