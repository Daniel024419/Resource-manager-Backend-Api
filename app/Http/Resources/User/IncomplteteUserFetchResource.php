<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Http\Resources\Skills\SkillsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Project\EmployeeProjectResource;
use App\Http\Resources\Specialization\SpecializationResource;

class IncomplteteUserFetchResource extends JsonResource
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
           'email' => $this->authInfo->email ?? null,
            'refId' => $this->refId ?? null,
            'bookable' => $this->bookable ?? null,
            'department' => $this->department->departmentInfo->name ?? null,
            'role' => $this->role->name ?? null,
            'created_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,
        ];
    }
}