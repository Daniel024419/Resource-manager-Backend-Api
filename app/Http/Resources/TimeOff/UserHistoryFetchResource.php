<?php

namespace App\Http\Resources\TimeOff;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserHistoryFetchResource extends JsonResource
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
        $days = Carbon::parse($this->startDate)->diffInWeekdays(Carbon::parse($this->endDate)) + 1;

        return [
            "refId" => $this->refId,
            "leaveType" => ucwords($this->typeDetail->name),
            "startDate" => $startDate,
            "endDate" => $endDate,
            "attachment" => env('AWS_S3_BASE_URL').$this->proof,
            "description" => $this->details,
            "days" => $days,
            "status" => $this->status,
        ];
    }
}
