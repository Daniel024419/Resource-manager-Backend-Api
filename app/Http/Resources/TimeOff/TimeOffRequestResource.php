<?php

namespace App\Http\Resources\TimeOff;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeOffRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'refId' => $this->refId,
            'name' => ucwords($this->user->employee->firstName.' '. $this->user->employee->lastName),
            'leaveType' => ucwords($this->typeDetail->name),
            'date' => Carbon::parse($this->startDate)->format('F j, Y') .' - '. Carbon::parse($this->endDate)->format('F j, Y'),
            'description' => ucwords($this->details),
            'requestedOn' => Carbon::parse($this->created_at)->format('j F'),
            'status' => $this->status,
            'attachment' => env('AWS_S3_BASE_URL').$this->proof
        ];
    }
}
