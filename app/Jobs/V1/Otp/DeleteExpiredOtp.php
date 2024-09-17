<?php

namespace App\Jobs\V1\Otp;

use Carbon\Carbon;
use App\Models\V1\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class DeleteExpiredOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Class DeleteExpiredOtp
     *
     * This class represents a job that deletes expired one-time passwords (OTP) in a queueable manner.
     * Implements the ShouldQueue interface, indicating that the job can be queued for asynchronous execution.
     */
    public function __construct()
    {
    }

    /**
     * delete all otp for the day
     * Execute the job.
     */
    public function handle(): void
    {
        Otp::where('expires_at', '<', Carbon::now())->delete();
    }
}