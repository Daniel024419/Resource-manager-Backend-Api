<?php

namespace App\Http\Resources\TimeOff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeOffTypeFetchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "refId"=> $this->refId,
            "id" => $this->id,
            "name"=>ucwords($this->name),
            "requiresProof"=>$this->showProof,
            "duration" => $this->duration,
        ];
    }
}