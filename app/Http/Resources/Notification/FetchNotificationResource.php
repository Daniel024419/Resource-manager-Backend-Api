<?php

namespace App\Http\Resources\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FetchNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'created_by' => ucfirst($this->recordMadeBy->firstName) .' '. ucfirst($this->recordMadeBy->lastName),
            'profilePicture' => env('AWS_S3_BASE_URL') .$this->recordMadeBy->profilePicture ?? null,
            'message' => $this->message,
            'time' => $this->created_at->diffForHumans(),
            'id' => $this->id,
            'read' => ($this->read == true) ? 'Yes': 'No',

        ];
    }
}