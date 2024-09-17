<?php

namespace App\Http\Resources\TimeOff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PeopleOnPendingFetchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = Carbon::parse($this->startDate)->format('F j, Y');
        $endDate = Carbon::parse($this->endDate)->format('F j, Y');

        $duration = Carbon::parse($this->startDate)->diffInWeekdays(Carbon::parse($this->endDate)) + 1;

        return [
            "refId" => $this->refId,
            "employee" => ucwords($this->user->employee->firstName) . ' ' . ucwords($this->user->employee->lastName),
            "leaveType" => ucwords($this->typeDetail->name),
            "startDate" => $startDate,
            "endDate" => $endDate,
            "duration" => $duration . " days",
            "proof" => env('AWS_S3_BASE_URL') . $this->proof
        ];
    }
}
