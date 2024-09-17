<?php

namespace App\Jobs\V1\projects;

use Carbon\Carbon;
use RuntimeException;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\V1\Notification\Notification;
use App\Service\V1\Notification\NotificationService;
use App\Notifications\V1\Projects\ProjectDeadLineNotification;

class ProjectDeadlineReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Define properties to hold data
    private $notificationService, $employeeProject, $user, $title;

    /**
     * Create a new instance of the notification job.
     *
     * @param NotificationService $notificationService - An instance of the NotificationService responsible for handling notifications.
     * @param mixed $employeeProject - The data related to the employee's project.
     * @param mixed $user - The user object associated with the notification.
     * @param string $title - The title or subject of the notification.
     */
    public function __construct(
        NotificationService $notificationService,
        array $employeeProject,
        $user,
        string $title
    ) {
        // Assign the provided values to the properties
        $this->notificationService = $notificationService;
        $this->employeeProject = $employeeProject;
        $this->user = $user;
        $this->title = $title;
    }



    public function handle()
    {
        try {

            foreach ($this->employeeProject as $key => $project) {

                $endDate = Carbon::parse($project->endDate);
                $startDate = Carbon::parse($project->startDate);

                $notificationData = [
                    'message' =>  'Deadline for ' . $project->name . ' project is approaching ' . $endDate->diffForHumans(),
                    'by' => $this->user->employee->id,
                    'employee_id' => $this->user->employee->id,
                ];

                Notification::create($notificationData);
                $notification = new ProjectDeadLineNotification(
                    $this->title,
                    $this->user->employee->firstName . ' ' . $this->user->employee->lastName,
                    $this->employeeProject,
                    'rm.io@amalitech.com',
                );
                $this->notificationService->projectAssignment($this->user, $notification);
            }
        } catch (RuntimeException $e) {

            Log::info($e);

            return new RuntimeException();
        }
    }
}
