<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Skills\SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Specialization\SpecializationResource;

class RegisterUserShowResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'email' => $this->email?? null,
        'refId' => $this->employee->refId ?? null,
        'department' => $this->employee->department->departmentInfo->name ?? null,
        'bookable' => $this->employee->bookable ?? null,
        'department' => $this->employee->department->departmentInfo->name ?? null,
        'skills' => SkillsResource::collection($this->employee->skills->unique('name') ?? collect()),
        'specializations' => SpecializationResource::collection($this->employee->specializations->unique('name') ?? collect()),
        'role' => $this->employee->role->name ?? null,


        ];
    }
}