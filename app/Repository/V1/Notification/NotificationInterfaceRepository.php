<?php

namespace App\Repository\V1\Notification;

use Illuminate\Database\Eloquent\Collection;

interface NotificationInterfaceRepository
{

    /**
     * Save new notification for users.
     *
     * @param array $notificationData
     * @return bool 
     */
    public function save(array $notificationData) : bool;

    /**
     * Fetch all notifications.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch(): Collection | null ;


    /**
     * Fetch all notifications by employee ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetchByEmployeeId() : Collection | null;


    /**
     * Mark notifications as read for a specific employee.
     *
     * @return bool
     */
    public function markAllAsRead();



    /**
     * Mark notifications as read for a specific employee.
     *
     * @param string $id
     * @return bool
     */
    public function markOneAsRead(string $id): bool;
}
