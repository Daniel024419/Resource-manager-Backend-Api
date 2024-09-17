<?php

namespace App\Repository\V1\Skills;

interface SkillsInterfaceRepository
{

    /**
     * Fetch all skills.
     *
     * @return mixed
     */
    public function fetch();


    /**
     * Fetch all skills by auth.
     *@param string $employee_id
     * @return mixed
     */
    public function fetchByAuth(string $employee_id);

    /**
     * Save new skills.
     *
     * @param array $skillsData
     * @return mixed
     */
    public function save(array $skillsData);

    /**
     * Update skills by skill ID.
     *
     * @param array $skillsData
     * @return mixed
     */
    public function updateById(array $skillsData);

    /**
     * Delete skills by skill ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;

     /**
     * Store skills for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @param int $rating
     * @return mix
     */
    public function store(array $data);

    /**
     * Update skills for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function updateByName(string $name, $employee_id): bool;

    /**
     * Update user skills for an employee by name.
     *
     * @param string $name
     * @param mixed $employee_id
     * @return bool
     */
    public function updateUserSkillsByName(string $name, $employee_id): bool;

     
}