<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Specialization\SpecializationResource;

class ArchivedUserFetchResource extends JsonResource
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
            'email' => $this->email ?? null,
            'role' => $this->employee->role->name ?? null,
            'firstName' => $this->employee->firstName ?? null,
            'lastName' => $this->employee->lastName ?? null,
            'profilePicture' => env('AWS_S3_BASE_URL') . $this->employee->profilePicture,
            'refId' => $this->employee->refId ?? null,
            'specializations' => SpecializationResource::collection($this->employee->specializations->unique('name') ?? collect()),
            'department' => $this->employee->department->departmentInfo->name ?? null,
            'archived_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
        ];
    }
}