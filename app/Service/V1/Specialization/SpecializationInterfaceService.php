<?php

namespace App\Service\V1\Specialization;

interface SpecializationInterfaceService
{
    /**
     * Fetch a single Specializations
     *
     * @return mixed
     */
    public function getASpecilization(int $specialization);
    /**
     * Fetch Specializations.
     *
     * @return mixed
     */
    public function fetch();

    /**
     * Store a new Specialization.
     *
     * @param $request
     * @return mixed
     */
    public function store($request);

    /**
     * Update a Specialization.
     *
     * @param $request
     * @return mixed
     */
    public function update($request);

    /**
     * Delete a Specialization.
     *
     * @param $request
     * @return mixed
     */
    public function delete($request);
}
