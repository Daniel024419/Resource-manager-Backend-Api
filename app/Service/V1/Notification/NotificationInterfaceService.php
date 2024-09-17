<?php

namespace App\Service\V1\Notification;

interface NotificationInterfaceService
{

    /**
     * Send OTP mail to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function sendOTP($user, $notification): bool;

    /**
     * Send account completion notification to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function accountCompletion($user, $notification): bool;

    /**
     * Send project assignment to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function projectAssignment($user, $notification): bool;

    /**
     * Send leave rejection or approved to the user.
     *
     * @param mixed $user The user to whom the notification is to be sent
     * @param mixed $notification The notification to be sent to the user
     * @return bool Whether the notification was sent successfully
     */
    public function timeOff($user, $notification): bool;

    /**
     * Mark all notifications as read for a specific employee.
     *
     * @return array Whether the operation was successful
     */
    public function markAllAsRead(): array;


    /**
     * Mark a notifications as read for a specific employee.
     *
     * @param Request  $request The ID of the employee for whom notifications should be marked as read
     * @return array Whether the operation was successful
     */
    public function markOneAsRead($request): array;
}
