<?php

namespace App\Jobs\V1\projects;

use Carbon\Carbon;
use App\Models\V1\User\User;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use App\Models\V1\Project\Project;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\V1\Project\EmployeeProject;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Service\V1\Notification\NotificationService;
use App\Notifications\V1\Projects\NewProjectReminderNotification;

class NewProjectReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $notificationService;

    /**
     * Create a new job instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the job for sending project reminder in 24hrs.
     */
    // In your NewProjectReminderJob class

    public function handle(Request $request): void
    {

        $users = User::whereHas('employee.employeeProjects.project', function ($query) {
            $query->whereColumn('employee_id', 'employee_id');
        })->get();

        $title = 'Project Assignment Reminder';

        // Assuming $users is a collection of User models
        $users->each(function ($user) use ($title) {
            // Get all projects assigned within 24hrs for the current user
            $projects = EmployeeProject::where('employee_id', $user->employee->id)
                ->whereBetween('created_at', [Carbon::now()->subDay(), Carbon::now()])
                ->get();

            // Process projects in chunks of 3
            $projects->chunk(3, function ($projectChunk) use ($user, $title) {
                foreach ($projectChunk as $project) {
                    // Retrieve project details
                    $projectDetails = Project::find($project->project_id);

                    // Call the project duration function
                    $duration = $this->duration($projectDetails->startDate, $projectDetails->endDate);

                    $notification = new NewProjectReminderNotification(
                        $title,
                        $user->employee->firstName . ' ' . $user->employee->lastName,
                        $projectDetails->name,
                        'Check them in your old mail at ' . $projectDetails->created_at->diffForHumans(),
                        $projectDetails->details,
                        $projectDetails->startDate,
                        $projectDetails->endDate,
                        'rm.io@amalitech.com',
                        $duration
                    );

                    $this->notificationService->projectAssignment($user, $notification);
                }
            });
        });
    }
    //calculate
    public function duration($startDate, $endDate)
    {
        $durationInDays = 0;
        $startDate = $startDate ? Carbon::parse($startDate) : null;
        $endDate = $endDate ? Carbon::parse($endDate) : null;

        if ($startDate && $endDate) {
            $durationInDays = $startDate->diffInDays($endDate);
        }
        $durationText = '';
        if ($durationInDays > 0) {
            if ($durationInDays >= 365) {
                $years = floor($durationInDays / 365);
                return $durationText .= $years . ($years > 1 ? ' years' : ' year');
            } elseif ($durationInDays >= 30) {
                $months = floor($durationInDays / 30);
                return $durationText .= $months . ($months > 1 ? ' months' : ' month');
            } elseif ($durationInDays >= 7) {
                $weeks = floor($durationInDays / 7);
                return  $durationText .= $weeks . ($weeks > 1 ? ' weeks' : ' week');
            } else {
                return $durationText .= $durationInDays . ($durationInDays > 1 ? ' days' : ' day');
            }
        } else {
            return $durationText = 'No duration available';
        }
    }
}