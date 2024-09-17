<?php

namespace App\Http\Resources\TimeOff;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PeopleOnLeaveFetchResource extends JsonResource
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
            "employee" => ucwords($this->user->employee->firstName) . ' ' . ucwords($this->user->employee->lastName),
            "startDate" => $startDate,
            "endDate" => $endDate,
            "duration" => $duration . " days",
        ];
    }
}