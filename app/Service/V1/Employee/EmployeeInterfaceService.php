<?php

namespace App\Service\V1\Employee;

/**
 * Interface EmployeeInterfaceService
 * @package App\Service\V1\Employee
 */
interface EmployeeInterfaceService
{

        /**
     * fetch all Bookable users
     *
     * @return mixed The user record, or a ModelNotFoundException if no user was found
     */

     public function fetchBookable($query);
     
    /**
     * Find an employee by email or employee name.
     *
     * @param string $email
     * @return string<int, string>
     */
    function findByemail(string $email);

    /**
     * Update an existing employee account setup.
     *
     * @param Request $request
     * @return mixed
     */
    public function accountSetup($request);

    /**
     * Setup an existing employee profile.
     *
     * @param Request $request
     * @return mixed
     */
    public function updateProfile($request);

    /**
     * Find an employee by refId.
     *
     * @param string $refId
     * @return string<int, string>
     */
    function findByRefId(string $refId);

    /**
     * Find an employee by authentication ID.
     *
     * @param int $id
     * @return string
     */
    public function findByAuthId(int $id);

    /**
     * Edit an existing employee profile (admin).
     *
     * @param Request $request
     * @return mixed
     */
    public function editProfile($request);

    /**
     * Admin update employee profile.
     *
     * @param Request $request
     * @return mixed
     */
    public function adminUpdateProfile($request);

}