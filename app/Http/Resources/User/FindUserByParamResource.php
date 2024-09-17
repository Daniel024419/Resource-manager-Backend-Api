<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class findByParamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id ?? null,
            'email' => $this->email ?? null,
            'refId' => $this->employee->refId ?? null,
            'firstName' => $this->employee->firstName  ?? null,
            'lastName' => $this->employee->lastName  ?? null,
            'phoneNumber' => $this->employee->phoneNumber  ?? null,
            'profilePicture' => env('AWS_S3_BASE_URL').$this->employee->profilePicture,
            'bookable' => $this->employee->bookable ?? null,
            'updated_at' => $this->employee->updated_at ? \Carbon\Carbon::parse($this->employee->updated_at)->format('F j, Y g:i A') : null,            'created_at' => $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('F j, Y g:i A') : null,

        ];
    }
}