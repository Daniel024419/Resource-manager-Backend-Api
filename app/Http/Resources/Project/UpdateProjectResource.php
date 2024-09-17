<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
class UpdateProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        $durationInDays = 0;

        if ($startDate && $endDate) {
            $durationInDays = $startDate->diffInDays($endDate);
        }

        $durationText = '';
        if ($durationInDays > 0) {
            if ($durationInDays >= 365) {
                $years = floor($durationInDays / 365);
                $durationText .= $years . ($years > 1 ? ' years' : ' year');
            } elseif ($durationInDays >= 30) {
                $months = floor($durationInDays / 30);
                $durationText .= $months . ($months > 1 ? ' months' : ' month');
            } elseif ($durationInDays >= 7) {
                $weeks = floor($durationInDays / 7);
                $durationText .= $weeks . ($weeks > 1 ? ' weeks' : ' week');
            } else {
                $durationText .= $durationInDays . ($durationInDays > 1 ? ' days' : ' day');
            }
        } else {
            $durationText = 'No duration available';
        }

        return [
            'name' => $this->projectCode ?? null,
            'projectType' => strtoupper($this->projectType) ?? null,
            'details' => $this->details ?? null,
            'billable' => $this->billable ?? null,
            'client' => $this->client->name ?? null,
            'startDate' => $this->startDate ? Carbon::parse($this->startDate)->format('F j, Y g:i A') : null,
            'endDate' => $this->endDate ? Carbon::parse($this->endDate)->format('F j, Y g:i A') : null,
            // 'duration' => $durationText,
            // 'created_by' => $this->createdByEmployee->firstName . ' ' . $this->createdByEmployee->lastName,
            // 'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
            // // 'archived_on' => $this->deleted_at ? Carbon::parse($this->deleted_at)->format('F j, Y g:i A') : null,
        ];
    }
}