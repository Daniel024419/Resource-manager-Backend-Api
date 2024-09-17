<?php

namespace App\Repository\V1\Department;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Department\Department;
use App\Models\V1\Department\UserDepartment;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DepartmentRepository implements DepartmentInterfaceRepository
{

    /**
     * Fetch all departments.
     *
     * @return mixed
     */
    public function fetch()
    {
        try {
            $Department = Department::orderBy('name','asc')->get();
            return $Department;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Save a new department.
     *
     * @param array $DepartmentData
     * @return mixed
     */
    public function save(array $departmentData)

    {
        try {
            DB::beginTransaction();
            // Create a new Department instance and save it
            $Department = Department::create($departmentData);
            DB::commit();
            return $Department;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    /**
     * Update a department by its ID.
     *
     * @param array $DepartmentData
     * @return mixed
     */
    public function updateById(array $departmentData)
    {
        try {
            DB::beginTransaction();
            // Find Department instance and update the hashed password
            Department::where('id', '=', $departmentData['id'])->update($departmentData);
            DB::commit();
            return true;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Delete a department by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        try {
            DB::beginTransaction();
            Department::where("id", "=", $id)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Store the department for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function storeByName(string $name, int $employee_id): bool
    {
        try {
            DB::beginTransaction();
            $department =  Department::whereRaw('LOWER(name) = ?', [$name])->first();
            UserDepartment::create(['employee_id' => $employee_id, 'department_id' => $department['id']]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Update the department for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function updateByName(string $name, int $employee_id): bool
    {
        try {
            DB::beginTransaction();
            $department =  Department::whereRaw('LOWER(name) = ?', [$name])->first();
            UserDepartment::where('employee_id', "=", $employee_id)->update(['department_id' => $department['id']]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}