<?php

namespace App\Repository\V1\Department;

interface DepartmentInterfaceRepository
{
    /**
     * Fetch all department.
     *
     * @return mixed
     */
    public function fetch();

    /**
     * Save a new department.
     *
     * @param array $DepartmentData
     * @return mixed
     */
    public function save(array $DepartmentData);

    /**
     * Update a department by its ID.
     *
     * @param array $DepartmentData
     * @return mixed
     */
    public function updateById(array $DepartmentData);

    /**
     * Delete a department by its ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;

    /**
     * Store the department for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function storeByName(string $name, int $employee_id): bool;

    /**
     * Update the department for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function updateByName(string $name, int $employee_id): bool;
}