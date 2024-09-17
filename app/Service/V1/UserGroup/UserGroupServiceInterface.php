<?php

namespace App\Service\V1\UserGroup;

interface UserGroupServiceInterface
{
    /**
     * Create a new user group.
     *
     * @param  array  $userGroupData
     * @return array
     */
    public function createGroup(array $userGroupData);

    /**
     * update a new user group.
     * @param string $refId
     * @param  array  $userGroupData
     * @return array
     */
    public function updateGroup(string $refId, array $userGroupData);

    /**
     * Find subgroups by groupable type and group ID.
     *
     * @param  string  $groupType
     * @param  int  $groupId
     * @return array
     */
    public function findSubgroupsByGroupable(string $groupType, int $groupId);

    /**
     * Find subgroups by reference ID.
     *
     * @param  string  $refId
     * @return array
     */
    public function findSubgroupsByRefId(string $refId);

    /**
     * Assign a user to a user group.
     *
     * @param  int  $id
     * @param  array  $userData
     * @return array
     */
    public function assignUserToUserGroup(int $id, array $userData);

    public function deleteGroup($refId);
}
