<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FetchProjectsExtensions extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $extensionsDetails = [];
        $daysAdded = 0;

        // Loop through each project history and retrieve its details.
        foreach ($this->projectHistories as $extension) {
            // Convert newDate and oldDate to Carbon instances
            $newDate = Carbon::parse($extension->newDate);
            $oldDate = Carbon::parse($extension->oldDate);

            // Calculate the difference in days
            $daysDifference = $newDate->diffInWeekdays($oldDate) + 1;
            $daysAdded += $daysDifference;

            $extensionsDetails[] = [
                'referenceId' => $extension->refId,
                'oldDate' => $oldDate->format('F j, Y'),
                'newDate' => $newDate->format('F j, Y'),
                'daysDifference' => $daysDifference,
                'reason' => $extension->reason,
                'extended_by' => $extension->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
            ];
        }

        $supposedDays = 0;
        $originalEndDate = $this->projectHistories->isNotEmpty() ? Carbon::parse($this->projectHistories->first()->oldDate) : Carbon::parse($this->endDate);
        $startDate = Carbon::parse($this->startDate);
        $supposedDays = $originalEndDate->diffInWeekdays($startDate) + 1;

        return [
            'name' => ucfirst($this->name) ?? null,
            'projectType' => strtoupper($this->projectType),
            'projectCode' => strtoupper($this->projectCode),
            'billable' => $this->billable ?? null,
            'startDate' => Carbon::parse($this->startDate)->format('F j, Y'),
            'newEndDate' => Carbon::parse($this->endDate)->format('F j, Y'),
            'originalEndDate' => $originalEndDate->format('F j, Y'),
            'totalProjectExtensions' => $this->projectHistories->count(),
            'totalDaysAdded' => $daysAdded,
            'supposedDays' => $supposedDays,
            'newprojectDuration' => $supposedDays + $daysAdded,
            'extensions' => $extensionsDetails,
        ];
    }
}