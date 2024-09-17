<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Notification\FetchNotificationResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Skills\SkillsResource;
use App\Http\Resources\Specialization\SpecializationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->email ?? null,
            'refId' => $this->employee->refId ?? null,
            'firstName' => $this->employee->firstName ?? null,
            'lastName' => $this->employee->lastName ?? null,
            'profilePicture' => env('AWS_S3_BASE_URL').$this->employee->profilePicture,
            'phoneNumber' => $this->employee->phoneNumber ?? null,
            'department' => $this->employee->department->departmentInfo->name ?? null,
            'specializations' => SpecializationResource::collection($this->employee->specializations->unique('name') ?? collect()),
            'skills' => SkillsResource::collection($this->employee->skills->unique('name') ?? collect()),
            'role' => $this->employee->role->name ?? null,
            'bookable' => $this->employee->bookable ?? null,
            'permissions' => new PermissionResource($this->employee->role)  ?? null,
            'changePassword' => $this->created_at == $this->updated_at,
        ];
    }
}