<?php

namespace App\Repository\V1\Specialization;

interface SpecializationInterfaceRepository
{

    /**
     * Fetch a single Specializations
     *
     * @return mixed
     */
    public function getASpecilization(int $specialization);
    /**
     * Fetch all specializations.
     *
     * @return mixed
     */
    public function fetch();

    /**
     * Save new specialization.
     *
     * @param array $specializationData
     * @return mixed
     */
    public function save(array $specializationData);

    /**
     * Update specialization by specialization ID.
     *
     * @param array $specializationData
     * @return mixed
     */
    public function updateById(array $specializationData);

    /**
     * Delete specialization by specialization ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;

    /**
     * Store specialization for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function storeByName(string $name, int $employee_id): bool;

    /**
     * Update specialization for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function updateByName(string $name, $employee_id): bool;
}
