<?php

namespace App\Repository\V1\Employee;

use App\Enums\Roles;
use App\Models\V1\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Employee\Employee;
use Illuminate\Support\Facades\Hash;
use App\Models\V1\Specialization\Specialization;
use App\Models\V1\Specialization\UserSpecialization;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository implements EmployeeInterfaceRepository
{
    /**
     * Fetch all bookable users from the database.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     * @throws ModelNotFoundException
     */
    public function fetchBookable($searchQuery)
    {
        try {
            $users = Employee::where('bookable', true)
                ->where(function ($query) use ($searchQuery) {
                    $query->where('firstName', 'LIKE', "%$searchQuery%")
                        ->orWhere('lastName', 'LIKE', "%$searchQuery%")
                        ->orWhere('phoneNumber', 'LIKE', "%$searchQuery%");
                })
                ->whereNotNull('firstName')
                ->whereNotNull('lastName')
                ->whereNotNull('phoneNumber')
                ->where('roleId', '<>', Roles::getRoleIdByValue(Roles::MGT->value))
                ->where('roleId', '<>', Roles::getRoleIdByValue(Roles::ADMIN->value))
                ->with('employeeProjects')
                ->get();

            // Filter out users with total work hours exceeding 8
            $filteredUsers = $users->filter(function ($user) {
                return $user->employeeProjects->sum('workHours') < 8;
            });

            return $filteredUsers;
        } catch (ModelNotFoundException $e) {
            // Throw an exception if no users are found
            throw new ModelNotFoundException();
        }
    }



    /**
     * Save a new Employee with an array of data
     *
     * @param array $employeeData
     * @return Employee
     */
    public function save(array $employeeData): Employee|null
    {
        try {
            DB::beginTransaction();
            $employee = Employee::create($employeeData);

            DB::commit();
            return $employee; // Return the associated employee
        } catch (\Exception $e) {

            DB::rollBack();
            // Handle the exception here (e.g., log the error)
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Find an employee by their email address or employee name
     *
     * @param string $email The email address or employee name of the employee to find
     * @return User|null The employee if found, or null if not found
     */
    public function findByemail(string $email) : User
    {
        try {
            $employee = User::where("email", $email)->first();

            return $employee;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException();
        }
    }

    /**
     * Find an employee by their email address or employee name
     *
     * @param string $email The email address or employee name of the employee to find
     * @return Employee|null The employee if found, or null if not found
     */
    public function findByParam(string $search_param) : Employee | null
    {
        try {
            $Employee = Employee::where("refId", $search_param)
                ->orwhere("userId", $search_param)->first();
            return $Employee;
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException();
        }
    }

    /**
     * Find an employee by their refId
     *
     * @param mixed $refId The refId of the employee to find
     * @return User|null The employee if found, or null if not found
     */
    public function findByRefId($refId): Employee | null
    {
        try {
            $employee = Employee::where("refId", $refId)->first();
            return $employee;
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * Find an employee by their auth id
     *
     * @param mixed $auth id The auth Id of the employee to find
     * @return Employee|null The employee if found, or null if not found
     */
    public function findByAuthId($id): Employee|null
    {
        try {
            $employee = Employee::where("userId", $id)->first();
            return $employee;
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * Update an existing employee with new data
     *
     * @param array $employeeData The new data for the employee
     * @return User|null The updated employee
     */
    public function updateByRefId(array $employeeData) : Employee | null
    {
        try {
            Log::info("data" . json_encode($employeeData));
            DB::beginTransaction();
            $refId = $employeeData['refId'];
            //remove id before update
            unset($employeeData['refId']);
            // find Employee instance and updated it
            $store = Employee::where('refId', $refId)->update($employeeData);
            if (!$store) {
                DB::rollBack();
                return null;
            }
            $employee = Employee::where('refId', $refId)->first();
            DB::commit();
            return $employee;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return null;
        }
    }

}