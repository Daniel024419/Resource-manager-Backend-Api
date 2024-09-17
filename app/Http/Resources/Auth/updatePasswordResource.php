<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\Permission\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class updatePasswordResource extends JsonResource
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
            'changePassword' => $this->created_at == $this->updated_at,
        ];
    }
}