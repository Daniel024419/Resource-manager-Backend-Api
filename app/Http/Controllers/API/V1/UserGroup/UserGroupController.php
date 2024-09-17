<?php

namespace App\Http\Controllers\API\V1\UserGroup;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserGroup\AssignUserRequest;
use App\Http\Requests\UserGroup\CreateGroupRequest;
use App\Http\Requests\UserGroup\UpdateGroupRequest;
use App\Service\V1\UserGroup\UserGroupServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class UserGroupController extends Controller
{
    public function __construct(public UserGroupServiceInterface $userGroupService)
    {
    }

    /**
     * Store a newly created group in storage.
     *
     * @param  \App\Http\Requests\UserGroup\CreateGroupRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createGroup(CreateGroupRequest $request)
    {
        try {
            $response = $this->userGroupService->createGroup($request->validated());
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a user group in storage.
     *
     * @param string $refId
     * @param  \App\Http\Requests\UserGroup\UpdateGroupRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGroup(UpdateGroupRequest $request, string $refId)
    {
        try {
            $response = $this->userGroupService->updateGroup($refId,$request->all());
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the groups specified specialization or department.
     *
     * @param  string  $section
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showUserGroups(string $section, int $id)
    {
        try {
            $response = $this->userGroupService->findSubgroupsByGroupable($section, $id);
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Assign a user to a user group.
     *
     * @param  string  $refId
     * @param  \App\Http\Requests\UserGroup\AssignUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignUserToUserGroup(string $refId, AssignUserRequest $request)
    {
        try {
            $findUserGroup = $this->userGroupService->findSubgroupsByRefId($refId);
            $response = $this->userGroupService->assignUserToUserGroup($findUserGroup['data']['id'], $request->validated(['employeeIds']));
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a user group.
     *
     * @param  string  $refId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteGroup(String $refId){
        try {
            $response = $this->userGroupService->deleteGroup($refId);
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
