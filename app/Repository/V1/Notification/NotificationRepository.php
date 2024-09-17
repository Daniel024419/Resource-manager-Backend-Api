<?php

namespace App\Repository\V1\Notification;

use Illuminate\Support\Facades\DB;
use App\Models\V1\Notification\Notification;
use Illuminate\Database\Eloquent\Collection;
use App\Repository\V1\Notification\NotificationInterfaceRepository;

class NotificationRepository implements NotificationInterfaceRepository
{

    /**
     * Save a new notification with the provided data.
     * @param array $notificationData
     * @return bool
     */
    public function save(array $notificationData): bool
    {
        try {
            DB::beginTransaction();
            // Create a new notification instance and save it
            Notification::create($notificationData);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Fetch all notifications.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch(): Collection | null
    {
        try {
            $notifications = Notification::all();
            return $notifications;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fetch all notifications by employee ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetchByEmployeeId(): Collection | null
    {
        try {
            $notifications = Notification::where('employee_id', auth()->user()->employee->id)->orderBy('created_at','desc')->get();
            return $notifications;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Mark all notifications as read for a specific employee.
     *
     * @return bool
     */
    public function markAllAsRead(): bool
    {
        try {
            // Retrieve all notifications for the given employee
            Notification::where('employee_id', auth()->user()->employee->id)->update(['read' => true]);
            return true;
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            return false;
        }
    }

    /**
     * Mark a notifications as read for a specific employee.
     *
     * @param string $id
     * @return bool
     */
    public function markOneAsRead(string $id): bool
    {
        try {
            // Retrieve  notifications for the given employee
            Notification::where('id', $id)->update(['read' => true]);
            return true;
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            return false;
        }
    }
}