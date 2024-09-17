<?php

namespace App\Jobs\V1\projects;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\V1\Employee\Employee;
use App\Models\V1\Project\EmployeeProject;
use App\Models\V1\Project\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Notification\Notification;

class ProjectArchiveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job to archive project that has ended.
     */
    public function handle(): void
    {
        $currentDate = now();

        // Get all projects that have ended
        $projects = Project::where('endDate', '<', $currentDate)->get();

        if ($projects->isNotEmpty()) {
            // Get the IDs of the projects and employee-project associations to be deleted
            $projectIds = $projects->pluck('id');
            $employeeProjectIds = EmployeeProject::whereIn('project_id', $projectIds)->pluck('id');

            // Iterate over each project
            foreach ($projects as $project) {
                // Get all employee-project associations for this project
                $employeeProjects = EmployeeProject::where('project_id', $project->id)->get();

                // Create a notification for each employee associated with this project
                foreach ($employeeProjects as $employeeProject) {
                    $notificationData = [
                        'message' => ucwords($project->name) . ' project ended at ' . $currentDate->format('F j, Y g:i A'),
                        'by' => 1,
                        'employee_id' => $employeeProject->employee_id,
                    ];
                    Notification::create($notificationData);
                }
            }

            // Delete projects and employee-project associations
            Project::whereIn('id', $projectIds)->delete();
            EmployeeProject::whereIn('id', $employeeProjectIds)->delete();
        }
    }
}