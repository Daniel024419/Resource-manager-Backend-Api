<?php

namespace App\Http\Resources\Skills;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FetchSkills extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'   => $this->id ?? null,
            'name' => ucwords($this->name) ?? null,
            'rating' => $this->rating ?? null,
        ];
    }
}