<?php

namespace App\Http\Resources\UserGroup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetUserGroupSubgroupsResource extends JsonResource
{
    /**
     * Get the group members.
     *
     * @return array
     */
    public function getGroupMembers(): array
    {
        $members = $this->groupMembers ?? [];

        $groupMembers = [];

        foreach ($members as $member) {
            $groupMembers[] = [
                'id' => $member->member->id,  
                'name' => ucwords($member->member->firstName . ' ' . $member->member->lastName),
                'profilePicture' => env('AWS_S3_BASE_URL').$member->member->profilePicture,
            ];
        }

        return $groupMembers;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'refId' => $this->refId,
            'name' => ucwords($this->name),
            'description' => $this->description,
            'createdBy' => ucwords($this->groupAdmin->firstName . ' ' . $this->groupAdmin->lastName),
            'groupMembers' => $this->getGroupMembers(),
        ];
    }
}
