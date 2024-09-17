<?php

namespace App\Service\V1\UserGroup;

use App\Http\Resources\UserGroup\CreateUserGroupResource;
use App\Http\Resources\UserGroup\GetUserGroupSubgroupsResource;
use App\Repository\V1\UserGroup\UserGroupRepositoryInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserGroupService implements UserGroupServiceInterface
{
    /**
     * Create a new UserGroupService instance.
     *
     * @param  UserGroupRepositoryInterface  $userGroupRepository
     * @return void
     */
    public function __construct(public UserGroupRepositoryInterface $userGroupRepository)
    {
    }

    /**
     * Assign a user to a user group.
     *
     * @param  int  $id
     * @param  array  $userData
     * @return array
     */
    public function assignUserToUserGroup(int $id, array $userData)
    {
        try {
            $employeeIds = $userData ?? [];
            $employees = [];
            foreach ($employeeIds as $employeeId) {
                $employees[] = [
                    'employeeId' => $employeeId,
                    'groupId' => $id,
                ];
            }
            $members = $this->userGroupRepository->addEmployeesToGroup($employees);
            if (!$members) {
                return [
                    'message' => 'Failed to add members to the group.',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }

            return [
                'message' => 'User assigned successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Unassign a user to a user group.
     *
     * @param  int  $id
     * @param  array  $userData
     * @return array
     */
    public function unassignUserToUserGroup(int $id, array $userData)
    {
        try {
            $employeeIds = $userData ?? [];
            $employees = [];
            foreach ($employeeIds as $employeeId) {
                $employees[] = [
                    'employeeId' => $employeeId,
                    'groupId' => $id,
                ];
            }
            $members = $this->userGroupRepository->removeEmployeesFromGroup($employees);
            if (!$members) {
                return [
                    'message' => 'Failed to remove members from the group.',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }

            return [
                'message' => 'User removed successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Find subgroups by groupable.
     *
     * @param  string  $groupType
     * @param  int  $groupId
     * @return array
     */
    public function findSubgroupsByGroupable(string $groupType, int $groupId)
    {
        try {

            $userGroups = $this->userGroupRepository->findSubgroupsByGroupable($groupType, $groupId);

            if (!$userGroups) {
                return [
                    'message' => 'Failed to find user group.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }
            return [
                'message' => 'User groups fetched successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => GetUserGroupSubgroupsResource::collection($userGroups),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Find subgroups by reference ID.
     *
     * @param  string  $refId
     * @return array
     */
    public function findSubgroupsByRefId(string $refId)
    {
        try {
            $userGroup = $this->userGroupRepository->findSubgroupsByRefId($refId);

            if (!$userGroup) {
                return [
                    'message' => 'User group not found.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }
            return [
                'message' => 'User group fetched successfully.',
                'status' => JsonResponse::HTTP_OK,
                'data' => $userGroup,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Create a new user group.
     *
     * @param  array  $userGroupData
     * @return array
     */
    public function createGroup(array $userGroupData)
    {
        try {
            $userGroupData['createdBy'] = auth()->user()->employee->id;
            $employeeIds = $userGroupData['employeeIds'] ?? [];

            $newGroup = $this->userGroupRepository->createGroup($userGroupData);

            if (!$newGroup) {
                return [
                    'message' => 'Failed to create user group.',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
            if (!$newGroup->wasRecentlyCreated) {
                return [
                    'message' => 'User group with name (' . $newGroup->name . ') already exist under ' . $newGroup->groupable->name,
                    'status' => JsonResponse::HTTP_CONFLICT,
                ];
            }

            $membersAdded = count($employeeIds);
            if ($membersAdded > 0) {
                $members = $this->assignUserToUserGroup($newGroup['id'], $employeeIds);
                if ($members['status'] !== JsonResponse::HTTP_OK && $members['status'] !== JsonResponse::HTTP_CREATED) {
                    $message = $membersAdded > 1 ? 'members were' : 'member was';
                    return [
                        'message' => "Failed to add $membersAdded $message to the " . $newGroup['name'] . ' group. Please consider assigning them directly if the issue persists.',
                        'status' => JsonResponse::HTTP_BAD_REQUEST,
                    ];
                }
            }

            $successMessage = 'User group created successfully.';
            if ($membersAdded > 0) {
                $message = $membersAdded > 1 ? 'members were' : 'member was';
                $successMessage .= " $membersAdded $message added to the group.";
            }

            return [
                'message' => $successMessage,
                'status' => JsonResponse::HTTP_CREATED,
                'data' => new CreateUserGroupResource($newGroup),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }



    /**
     * Update a user group.
     * @param string $refId
     * @param  array  $userGroupData
     * @return array
     */
    public function updateGroup(string $refId, array $userGroupData)
    {
        try {
            $userGroupData['refId'] = $refId;
            $employeeIds = $userGroupData['employeeIds'] ?? [];

            unset($userGroupData['id'], $userGroupData['modelClass'], $userGroupData['groupId'], $userGroupData['groupFor'], $userGroupData['employeeIds']);

            $newGroup = $this->userGroupRepository->updateGroup($userGroupData);

            if (!$newGroup) {
                return [
                    'message' => 'Failed to update the user group.',
                    'status' => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }

            $membersAffected = count($employeeIds);
            if ($membersAffected > 0) {
                $members = $this->unassignUserToUserGroup($newGroup['id'], $employeeIds);
                if ($members['status'] !== JsonResponse::HTTP_OK && $members['status'] !== JsonResponse::HTTP_CREATED) {
                    $message = $membersAffected > 1 ? 'members from' : 'member from';
                    return [
                        'message' => "Failed to remove $membersAffected $message the " . $newGroup['name'] . ' group.',
                        'status' => JsonResponse::HTTP_BAD_REQUEST,
                    ];
                }
            }

            $successMessage = 'User group updated successfully.';
            if ($membersAffected > 0) {
                $message = $membersAffected > 1 ? 'members were' : 'member was';
                $successMessage .= " $membersAffected $message removed from the group.";
            }

            return [
                'message' => $successMessage,
                'status' => JsonResponse::HTTP_CREATED,
                'data' => new CreateUserGroupResource($newGroup),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * Delete a new user group.
     *
     * @param  string  $refId
     * @return array
     */
    public function deleteGroup($refId)
    {
        try {
            $userGroup = $this->userGroupRepository->deleteGroup($refId);

            if (!$userGroup) {
                return [
                    'message' => 'User group not deleted.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }
            return [
                'message' => 'User group deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
}