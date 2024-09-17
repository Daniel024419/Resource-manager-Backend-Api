<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateUserPasswordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // "id" => $this->id ?? null,
            "email" => $this->email ?? null,
            'refId' => $this->employee->refId ?? null,
            'firstName' => $this->employee->firstName  ?? null,
            'lastName' => $this->employee->lastName  ?? null,
            'phoneNumber' => $this->employee->phoneNumber  ?? null,
            'profilePicture' => env('AWS_S3_BASE_URL').$this->employee->profilePicture,
        ];
    }
}
