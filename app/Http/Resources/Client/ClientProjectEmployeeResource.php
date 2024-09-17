<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientProjectEmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $fullName = trim("{$this->employee->firstName} {$this->employee->lastName}");

        return [
            'name' => $fullName !== '' ? $fullName : null,  
        ];
    }
}