<?php

namespace App\Http\Resources\TimeOff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateTimeOffTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "refId" => $this->refId,
            "name" => ucwords($this->name),
            "duration" => $this->duration,
            "showProof" => $this->showProof,
        ];
    }
}
