<?php

namespace App\Service\V1\Project;

use App\Http\Requests\Project\ProjectIdRequest;


interface ProjectInterfaceService
{
    /**
     * get all project extentions
     * @param $query
     * @return mixed Projects
     */
    public function extensions($query);

    /**
     * extend project time line
     *
     * @param Request $request The request object
     * @return array The result of extending the already existing project
     */
    public function extendTimeLine($request);

    /**
     * @return mixed projects
     */
    public function projectsAssigned();

    /**
     * Retrieve project information including total upcoming and active projects.
     *
     * @return array
     */
    public function projectInfo();
    /**
     * @param $request
     * @return mixed Projects
     */
    public function fetch($query);

    /**
     * save $Project
     * @return array< int , strin>
     */
    function save($ProjectData);

    /**
     * delete/archive a Project
     * @param $request
     * @return array
     */

    public function delete($request);

    /**
     * update Project data
     * @param $request
     * @return mixed
     */
    public function update($request);

    /**
     * edit employee project schedule .
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function scheduleEdit($request): array;

    /**
     * search Project data
     * @param $request
     * @return mixed
     */
    public function search($request): array;


    /**
     * Find a project by project ID.
     *
     * @param mixed $request
     *     The request containing the project ID or relevant information.
     *
     * @return mixed
     *     The project information or null if the project is not found.
     */

    public function findByProjectId($request);

       /**
     * Assigns a project to users.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function assign($request): array;

     /**
     * assigns a project to a user
     * @param $request
     * @return mixed
     */
    public function unAssign($request): array;

        /**
     * Sends project assignment notifications to the assigned users.
     *
     * @param array  $refIds        The array of reference IDs of the assigned users.
     * @param mixed  $project       The project object associated with the assignment.
     * @param array  $assignedUsers The array of names of the assigned users.
     * @param mixed  $request       The request object containing user information.
     *
     * @return void
     */
    public function sendProjectAssignmentNotifications(array $refIds, $project, array $assignedUsers);

    /**
     * get all employee projects by auth
     * @return mixed Projects
     */
    public function employeeProject();


    //archive operations
     /**
     * Fetches archives.
     *
     * This method retrieves archived data, providing access to historical records.
     *
     * @return mixed
     *     The result of fetching archived data.
     */
    public function archivesFetch();

    /**
     * unarchive project
     * @param ProjectIdRequest $request
     * @var $request
     */
    public function archivesRestore($request);

    /**
     * delete archived projects
     * @param Request $request
     * @var $ProjectIdRequest $request
     * @return array
     */
    public function archivesDelete($request);

    /**
     * search archived projects
     * @param ProjectNameRequest $request
     * @var $request , $ProjectId
     * @return array
     */
    public function archiveseSarch($request);


}
