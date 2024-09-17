<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class EmployeeProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name'=> ucfirst($this->project->name) ?? null,
            'projectCode' => $this->project->projectCode ?? null,
            'client' => $this->project->client->name ?? null,
            'workHours'=>$this->project->employeeProjects[0]->workHours,
            'scheduleId'=>$this->project->employeeProjects[0]->id,
            'startDate' => Carbon::parse($this->project->startDate)->toIso8601String() ?? null,
            'endDate' => Carbon::parse($this->project->endDate)->toIso8601String() ?? null,

        ];
    }
}