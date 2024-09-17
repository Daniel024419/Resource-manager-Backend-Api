<?php

namespace App\Repository\V1\UserGroup;

use App\Models\V1\Department\Department;
use App\Models\V1\Specialization\Specialization;
use App\Models\V1\UserGroup\Group;
use App\Models\V1\UserGroup\UserGroup;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserGroupRepository implements UserGroupRepositoryInterface
{
    /**
     * Find subgroups by groupable type and group ID.
     *
     * @param  string  $groupType
     * @param  int  $groupId
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findSubgroupsByGroupable(string $groupType, int $groupId)
    {
        try {

            switch ($groupType) {
                case 'specialization':
                    $parentModel = Specialization::find($groupId);
                    break;
                case 'department':
                    $parentModel = Department::find($groupId);
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid group type specified.');
            }

            if (!$parentModel) {
                throw new \RuntimeException('Parent model not found.');
            }

            $subgroups = $parentModel->groups()->where('createdBy', auth()->user()->employee->id)->get();


            return $subgroups;
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }


    /**
     * Find subgroups by reference ID.
     *
     * @param  string  $refId
     * @return \App\Models\V1\UserGroup\Group|null
     */
    public function findSubgroupsByRefId(string $refId)
    {
        try {
            DB::beginTransaction();
            $userGroup = Group::where('refId', $refId)->first();
            DB::commit();
            return $userGroup;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }

    /**
     * Create a new user group.
     *
     * @param  array  $userGroupData
     * @return \App\Models\V1\UserGroup\Group|null
     */
    public function createGroup(array $userGroupData)
    {
        try {
            $groupFor = strtolower($userGroupData['groupFor']);
            $groupId = $userGroupData['groupId'];
            $groupName = $userGroupData['name'];


            DB::beginTransaction();

            switch ($groupFor) {
                case 'specialization':
                    $parentModel = Specialization::find($groupId);
                    break;
                case 'department':
                    $parentModel = Department::find($groupId);
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid group type specified.');
            }

            if (!$parentModel) {
                throw new \RuntimeException('Parent model not found.');
            }

            $group = $parentModel->groups()->firstOrCreate(['name' => $groupName, 'createdBy' => $userGroupData['createdBy']], $userGroupData);

            DB::commit();
            return $group;
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error($e->getMessage());
            throw $e;
        }
    }


    /**
     * Update an existing user group with the specified data.
     *
     * @param  int $groupId
     * @param  array  $userGroupData
     * @return \App\Models\V1\UserGroup\Group|null
     */
    public function updateGroup(array $userGroupData)
    {
        try {
            DB::beginTransaction();

            $group = Group::where('refId', $userGroupData['refId'])->first();

            if ($group) {
                if (isset($userGroupData['name'])) {
                    $group->name = $userGroupData['name'];
                }
                if (isset($userGroupData['description'])) {
                    $group->description = $userGroupData['description'];
                }
                $group->save();
            }

            DB::commit();
            return $group;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            return null;
        }
    }


    /**
     * Add employees to a group.
     *
     * @param  array  $employees
     * @return array
     * @throws \Exception
     */
    public function addEmployeesToGroup(array $employees)
    {
        try {
            DB::beginTransaction();

            $insertedRecords = [];

            foreach ($employees as $employee) {
                $insertedRecord = UserGroup::firstOrCreate([
                    'employeeId' => $employee['employeeId'],
                    'groupId' => $employee['groupId'],
                ]);
                $insertedRecords[] = $insertedRecord;
            }

            DB::commit();

            return $insertedRecords;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw new Exception('Failed to add employees to group: ' . $e->getMessage());
        }
    }

    /**
     * Remove employees from a group.
     *
     * @param  array  $employees
     * @return array
     * @throws \Exception
     */
    public function removeEmployeesFromGroup(array $employees)
    {
        try {
            DB::beginTransaction();

            $deletedRecords = [];

            foreach ($employees as $employee) {
                // Find the record to be deleted
                $recordToDelete = UserGroup::where([
                    'employeeId' => $employee['employeeId'],
                    'groupId' => $employee['groupId'],
                ])->first();

                // If the record exists, delete it
                if ($recordToDelete) {
                    $recordToDelete->delete();
                    $deletedRecords[] = $recordToDelete;
                }
            }

            DB::commit();

            return $deletedRecords;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw new Exception('Failed to remove employees from group: ' . $e->getMessage());
        }
    }

    public function deleteGroup($refId)
    {
        try {
            DB::beginTransaction();
            $userGroup = Group::where('refId', $refId)->delete();
            DB::commit();
            return $userGroup;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }
}
