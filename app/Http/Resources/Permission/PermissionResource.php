<?php

namespace App\Http\Resources\Permission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "can_add_user" => $this->can_add_user,
            "can_add_manager" => $this->can_add_manager,
            "can_update_user_role" => $this->can_update_user_role,
            "can_create_project" => $this->can_create_project,
            "can_create_client" => $this->can_create_client,
            "can_assign_user_to_project" => $this->can_assign_user_to_project,
            "can_assign_client_to_project" => $this->can_assign_client_to_project,
            "can_assign_user_to_department" => $this->can_assign_user_to_department,
            "can_assign_user_to_specialization" => $this->can_assign_user_to_specialization,
            "can_add_user_to_group" => $this->can_add_user_to_group,
        ];
    }
}