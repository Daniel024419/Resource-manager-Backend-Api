<?php

namespace App\Jobs\V1\Holiday;

use App\Models\V1\Holiday\Holiday;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class HolidayJob implements ShouldQueue
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
     * Execute the job.
     */

    public function handle(): void
    {
        $countries = [
            'Africa/Accra' => 'GH',
            'Europe/Berlin' => 'DE',
            'Africa/Kigali' => 'RW'
        ];

        $currentYear =  date('Y') + 1;

        foreach ($countries as $timezone => $countryCode) {
            $response = Http::get(env('HOLIDAY_ACCESS_API_URL'), [
                'api_key' => env('HOLIDAY_ACCESS_API_KEY'),
                'country' => $countryCode,
                'year' => $currentYear,
            ]);

            $holidays = $response->json('response.holidays');

            if (!empty($holidays)) {
                foreach ($holidays as $holiday) {
                    Holiday::firstOrcreate([
                        'holiday' => $holiday['name'],
                        'date' => $holiday['date']['iso'],
                        'timeZone' => $timezone,
                    ]);
                }
            }
        }
    }
}
