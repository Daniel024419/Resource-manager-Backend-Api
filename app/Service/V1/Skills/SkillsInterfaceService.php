<?php

namespace App\Service\V1\Skills;

interface SkillsInterfaceService
{

    /**
     * @param $request
     * @return mixed
     */
    public function fetch();

    /**
     * fetch all  skills by authenticated
     * @param $request
     * @return mixed skills
     */

    public function fetchByAuth($request);

    /**
     * @param $request
     * @return mixed
     */
    public function store($request);

    /**
     * @param $request
     * @return mixed
     */
    public function update($request);
    /**
     * @param $request
     * @return mixed
     */
    public function delete($request);
}