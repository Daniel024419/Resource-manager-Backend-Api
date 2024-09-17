<?php

namespace App\Jobs;

use App\Enums\Roles;
use App\Models\V1\User\User;
use Illuminate\Bus\Queueable;
use App\Enums\defaultPassword;
use App\Enums\AdminDefaultPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\V1\Auth\SendAdminDefaultCredentials;
use App\Service\V1\Notification\NotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SystemUpNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected  $notificationService;

    /**
     * Create a new job instance.
     */
    public function __construct(
        NotificationService $notificationService,

    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $title = "Admin Account setup";
        if (Schema::hasTable('users') && Schema::hasTable('roles')) {
            $user = User::whereColumn('users.created_at', '=', 'users.updated_at')
                ->whereHas('employee', function ($query) {
                    $query->where('roleId', Roles::getRoleIdByValue(Roles::ADMIN->value));
                    $query->whereNotNull('firstName');
                    $query->whereNotNull('lastName');
                    $query->whereNotNull('phoneNumber');
                })
                ->with('employee')
                ->first();
            if ($user) {
                $notification = new SendAdminDefaultCredentials(
                    $title,
                    $user->email,
                    $user->employee->firstName,
                    $user->employee->lastName,
                    AdminDefaultPassword::password->value
                );

                if (!Cache::has('notification_sent_' . $user->id)) {
                    if ($this->notificationService->SendAdminDefaultCredentials($user, $notification)) {
                        Cache::put('notification_sent_' . $user->id, true, now()->addDay());
                    }
                }
            }
        }
    }
}