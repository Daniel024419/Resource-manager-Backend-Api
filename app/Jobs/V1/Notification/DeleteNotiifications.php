<?php

namespace App\Jobs\V1\Notification;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\V1\Notification\Notification;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeleteNotiifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * delete all notification.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job to delete all notifications every month.
     */
    public function handle(): void
    {
        // Calculate the date 30 days ago
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Delete notifications older than 30 days
        Notification::where('created_at', '<', $thirtyDaysAgo)->delete();
        
    }
}
