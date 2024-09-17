<?php

namespace App\Http\Resources\TimeTrack;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetTimeTracksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'task' => $this->task,
            'projectName' => $this->employeeProject?->project->name,
            'projectId' => $this->employeeProjectId,
            'billable' => $this->employeeProject?->project->billable,
            'date' => $this->date,
            'startTime' => $this->startTime->format('H:i:s'),
            'endTime' => $this->endTime->format('H:i:s'),
    
        ];
    }
}
