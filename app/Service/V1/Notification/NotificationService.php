<?php

namespace App\Service\V1\Notification;

use App\Repository\V1\Employee\EmployeeRepository;
use App\Repository\V1\Notification\NotificationInterfaceRepository;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService implements NotificationInterfaceService
{

    protected $notificationRepository, $EmployeeRepository;
    /**
     * NotificationService Constructor.
     *
     * This constructor initializes the NotificationService object.
     * It takes instances of the NotificationRepository and EmployeeRepository classes as dependency injections.
     *
     * @param NotificationInterfaceRepository $notificationRepository
     *     An instance of the NotificationRepository class, providing data access methods for notification-related operations.
     *     This instance will be injected into the NotificationService.
     */
    public function __construct(NotificationInterfaceRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;

    }

    /**
     * Send account completion notification to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function accountCompletion($user, $notification): bool
    {
        try {
            // Send the notification to the notifiable object
            Notification::sendNow($user, $notification);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Send OTP mail to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function sendOTP($user, $notification): bool
    {
        try {
            // Send the notification to the notifiable object
            Notification::sendNow($user, $notification);
            // Return true to indicate success
            return true;
        } catch (Exception $e) {
            return false;
        }
    }



    /**
     * Send project assignment to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function projectAssignment($user, $notification): bool
    {
        try {

            // Send the notification to the notifiable object
            Notification::sendNow($user, $notification);
            // Return true to indicate success
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Send leave rejection or approved to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function timeOff($user, $notification): bool
    {
        try {
            Notification::sendNow($user, $notification);
            return true;
        } catch (Exception $e) {
            Log::info($e);
            return false;
        }
    }

    /**
     * Mark all notifications as read for a specific employee.
     *
     * @return array Whether the operation was successful
     */
    public function markAllAsRead(): array
    {
        try {

            $notification =  $this->notificationRepository->markAllAsRead();

            if (!$notification) {
                return [
                    'message' => "Notification not marked read successfully, Please try again"
                ];
            }
            return [
                'message' => "Notification marked read successfully"
            ];
        } catch (Exception $e) {
            return [
                'message' => "Notification not marked read successfully, Please try again"
            ];
        }
    }


    /**
     * Mark a notifications as read for a specific employee.
     *
     * @param string $employee_id The ID of the employee for whom notifications should be marked as read
     * @return array Whether the operation was successful
     */
    public function markOneAsRead($request): array
    {
        try {
            $cleanData = $request->validated();

            $notification = $this->notificationRepository->markOneAsRead($cleanData['notification_id']);
            if (!$notification) {
                return [
                    'message' => "Notification not marked read successfully, Please try again"
                ];
            }
            return [
                'message' => "Notification marked read successfully"
            ];
        } catch (Exception $e) {
            return [
                'message' => "Notification not marked read successfully, Please try again"
            ];
        }
    }


    /**
     * send admin default credentials.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function SendAdminDefaultCredentials($user, $notification): bool
    {
        try {
            Notification::sendNow($user, $notification);
            return true;
        } catch (Exception $e) {
            Log::info($e);
            return false;
        }
    }
}
