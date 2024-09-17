<?php

namespace App\Repository\V1\Employee;

use App\Models\V1\User\User;
use App\Models\V1\Employee\Employee;

interface EmployeeInterfaceRepository
{

    /**
     * Fetch all bookable users from the database.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     * @throws ModelNotFoundException
     */
    public function fetchBookable($query);


    /**
     *save new $Employees
     * @param array $EmployeeData
     * @return Employee|null The employee if found, or null if not found
     */
    public function save(array $EmployeeData): Employee | null;


    /**
     *@retrun Employee by email
     *@param $id , $Employeename , $email
     * @return User|null The employee if found, or null if not found
     */

    public function findByemail(string $email) : User | null;


    //findByParam

    /**
     *@retrun Employee
     *@param $search_param string
     * @return Employee|null The employee if found, or null if not found
     */
    public function findByParam(string $search_param): Employee | null;

    /**
     *@retrun Employee
     *@param array $EmployeeData
     * @return Employee|null The employee if found, or null if not found
     */

    public function updateByRefId(array $EmployeeData): Employee | null;


    /**
     * find by refId
     *@param $refId string
     * @return Employee|null The employee if found, or null if not found
     */

    function findByRefId($refId) : Employee | null;

    /**
     * Find an employee by their auth id
     *
     * @param mixed $auth id The auth Id of the employee to find
     * @return Employee|null The employee if found, or null if not found
     */
    public function findByAuthId($id) : Employee | null;
}