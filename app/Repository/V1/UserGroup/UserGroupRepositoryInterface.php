<?php

namespace App\Repository\V1\UserGroup;

interface UserGroupRepositoryInterface
{
    /**
     * Create a new user group.
     *
     * @param  array  $userGroupData
     * @return \App\Models\V1\UserGroup\Group|null
     */
    public function createGroup(array $userGroupData);

    /**
     * Update an existing user group with the specified data.
     *
     * @param  int $groupId
     * @param  array  $userGroupData
     * @return \App\Models\V1\UserGroup\Group|null
     */
    public function updateGroup(array $userGroupData);
    /**
     * Find subgroups by groupable type and group ID.
     *
     * @param  string  $groupType
     * @param  int  $groupId
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findSubgroupsByGroupable(string $groupType, int $groupId);

    /**
     * Find subgroups by reference ID.
     *
     * @param  string  $refId
     * @return \App\Models\V1\UserGroup\Group|null
     */
    public function findSubgroupsByRefId(string $refId);

    /**
     * Add employees to a group.
     *
     * @param  array  $employees
     * @return array
     * @throws \Exception
     */
    public function addEmployeesToGroup(array $employees);

    /**
     * Remove employees from a group.
     *
     * @param  array  $employees
     * @return array
     * @throws \Exception
     */
    public function removeEmployeesFromGroup(array $employees);
    
    public function deleteGroup($refId);
}
