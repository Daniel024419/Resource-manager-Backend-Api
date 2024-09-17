<?php

namespace App\Repository\V1\TimeOff;

interface TimeOffRepositoryInterface
{

    /**
     * Reassign a leave request to be managed by another.
     *
     * @return mixed
     */
    public function reassignLeaveRequest(array $data);

    /**
     * Retrieve a users upcoming timeOffs, employees currently on timeoff and employees returning from time off.
     *
     * @return mixed
     */
    public function timeOffInfo();

    public function timeOffRequest();

    /**
     * Retrieve all leave types.
     *
     * @return mixed
     */
    public function leaveTypes();

    /**
     * Retrieve pending leave requests.
     *
     * @return mixed
     */
    public function pendingLeaves();

    /**
     * Manage the approval or rejection of a leave request.
     *
     * @param string $action The action to perform (approve or reject).
     * @param string $refId The reference ID of the leave request.
     * @return \App\Models\TimeOffRequests|null The updated leave request if successful; otherwise, null.
     */
    public function manageLeave(string $action, string $refId);


    /**
     * Get leave history of a user.
     *
     * @return mixed
     */
    public function leaveHistory();

    /**
     * Retrieve information about employees on leave.
     *
     * @return mixed
     */
    public function employeesOnLeave();

    /**
     * Create a new leave type.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update details of a leave type.
     *
     * @param array $data
     * @param string $refId
     * @return mixed
     */
    public function update(array $data, string $refId);

    /**
     * Delete a leave type by its ID.
     *
     * @param string $refId
     * @return mixed
     */
    public function deleteLeaveType(string $refId);

    /**
     * Find a leave type by its reference ID.
     *
     * @param string $refId
     * @return mixed
     */
    public function findLeaveTypeByRefId(string $refId);

    /**
     * Check if the user has used up their allocated annual leave.
     *
     * @param int $leaveType The ID of the leave type to check.
     * @param int $userId The ID of the user for whom to check the leave allocation.
     * @return bool True if the user has used up their allocated annual leave, false otherwise.
     */
    public function hasUsedUpAnnualLeave($leaveType, $userId);
    /**
     * Book leave for an employee.
     *
     * @param array $data
     * @return mixed
     */
    public function bookLeave(array $data);


    /**
     * Fetch all past holidays
     */
    public function passedHolidays();
}
