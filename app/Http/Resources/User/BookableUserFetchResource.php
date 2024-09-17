<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Skills\SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Project\EmployeeProjectResource;
use App\Http\Resources\Specialization\SpecializationResource;

class BookableUserFetchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
{
    return [
        'userId' => $this->userId ?? null,
        'refId' => $this->refId ?? null,
        'firstName' => $this->firstName ?? null,
        'lastName' => $this->lastName ?? null,
        'profilePicture' => env('AWS_S3_BASE_URL').$this->profilePicture,
        'department' => $this->department->departmentInfo->name ?? null,
        'specializations' => SpecializationResource::collection($this->specializations?? collect()),
        'skills' => SkillsResource::collection($this->skills ?? collect()),
        'bookable' => $this->bookable ?? null,
        'created_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
    ];
}

}