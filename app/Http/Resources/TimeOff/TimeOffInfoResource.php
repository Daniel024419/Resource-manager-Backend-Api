<?php

namespace App\Http\Resources\TimeOff;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeOffInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title' => 'timeoff',
            'name' => ucwords($this->user->employee->firstName . ' ' . $this->user->employee->lastName),
            'profilePicture' => env('AWS_S3_BASE_URL').$this->user->employee->profilePicture,
            'startDate' => Carbon::parse($this->startDate)->format('jS F Y'),
            'endDate' => Carbon::parse($this->endDate)->format('jS F Y'),
        ];
    }
}
