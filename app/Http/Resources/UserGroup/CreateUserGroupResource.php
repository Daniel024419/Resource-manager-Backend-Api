<?php

namespace App\Http\Resources\UserGroup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateUserGroupResource extends JsonResource
{
    public function getGroupMembers()
    {
        $members = $this->groupMembers;
        $groupMembers = [];

        foreach ($members as $member) {
            $groupMembers[] = [
                'profilePicture' =>$member->member->profilePicture,
                'name' => ucwords($member->member->firstName . ' '.$member->member->lastName),
            ];
        }

        return $groupMembers;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'refId' => $this->refId,
            'name' => ucwords($this->name),
            'createdBy' => ucwords($this->groupAdmin->firstName.' '.$this->groupAdmin->lastName),
            'refId' => $this->refId,
            'groupMembers' => $this->getGroupMembers()
        ];
    }
}
