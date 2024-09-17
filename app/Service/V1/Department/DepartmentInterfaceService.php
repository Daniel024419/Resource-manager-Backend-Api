<?php

namespace App\Service\V1\Department;

/**
 * Interface DepartmentInterfaceService
 * @package App\Service\V1\Department
 */
interface DepartmentInterfaceService
{
    /**
     * Fetch all departments.
     *
     * @return array
     */
    public function fetch(): array;

    /**
     * Store a new department.
     *
     * @param $request
     * @return array
     */
    public function store($request): array;

    /**
     * Update an existing department.
     *
     * @param $request
     * @return mixed
     */
    public function update($request): array;

    /**
     * Delete a department.
     *
     * @param $request
     * @return mixed
     */
    public function delete($request): array;
}