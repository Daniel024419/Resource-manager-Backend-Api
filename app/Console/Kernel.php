<?php

namespace App\Console;

use App\Jobs\V1\Holiday\HolidayJob;
use DateTimeZone;
use Carbon\Carbon;
use App\Models\V1\User\User;
use Illuminate\Support\Facades\Log;
use App\Jobs\V1\Otp\DeleteExpiredOtp;
use App\Service\V1\Project\ProjectService;
use App\Repository\V1\Users\UserRepository;
use Illuminate\Console\Scheduling\Schedule;
use App\Repository\V1\Client\ClientRepository;
use App\Jobs\V1\projects\NewProjectReminderJob;
use App\Jobs\V1\projects\LateProjectReminderJob;
use App\Repository\V1\Project\ProjectRepository;
use App\Jobs\V1\projects\DailyProjectReminderJob;
use App\Jobs\V1\Notification\DeleteNotiifications;
use App\Jobs\V1\projects\ProjectArchiveJob;
use App\Repository\V1\Employee\EmployeeRepository;
use App\Service\V1\Notification\NotificationService;
use App\Repository\V1\Notification\NotificationRepository;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Instantiate ProjectService with all dependencies
        $projectService = new ProjectService(
            new ProjectRepository(),
            new ClientRepository(),
            new NotificationRepository(),
            new NotificationService(
                new NotificationRepository(),
                new EmployeeRepository()
            ),
            new UserRepository(),
            new EmployeeRepository()
        );

        // Delete expired OTPs every day
        $schedule->job(new DeleteExpiredOtp())->daily();

        // Schedule the NewProjectReminderJob to run daily at 11:41
        //$schedule->job(new NewProjectReminderJob(app(NotificationService::class)))->dailyAt('11:41');

        // Schedule the deleteNotification function to run monthly
        $schedule->job(new DeleteNotiifications())->monthly();

        $schedule->job(new ProjectArchiveJob())->dailyAt('05:00');

        // Call the checkProjectTimeLines method on the instantiated ProjectService every minute
        $projectService->checkProjectTimeLines($schedule);

        // Run the Holiday Job to load all upcoming holiday for the next year
        $schedule->job(new HolidayJob())->yearlyOn(12, 1, '23:57');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
