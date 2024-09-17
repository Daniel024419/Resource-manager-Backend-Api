<?php

namespace App\Repository\V1\Project;


interface ProjectInterfaceRepository
{

    /**
     * extend project time line
     * @param array $projectData
     * @return ProjectHistory|null
     * @throws ModelNotFoundException
     */
    public function extendTimeLine(array $projectData);

    /**
     * Fetch all projects a user is assigned to
     */
    public function projectsAssigned();

     /**
     * update a Project requirement with an array of data.
     * @param array $requirementData
     * @return Project|null
     * @throws ModelNotFoundException
     */
    public function updateProjectRequirement($requirementData);

    /**
     * Save project requirement.
     *
     * @param array $requirementData
     * @return mixed
     */
    public function saveProjectRequirement(array $requirementData);

    /**
     * Retrieve information about projects.
     *
     * @return array
     */
    public function projectInfo();

    /**
     * Fetch all projects based on a query.
     *
     * @param mixed $query
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch($query);

    /**
     * Save a new project.
     *
     * @param array $projectData
     * @return mixed
     */
    function save(array $projectData);

    /**
     * Update a project by its ProjectId.
     *
     * @param array $projectData
     * @return mixed
     */
    public function updateByProjectId(array $projectData);

    /**
     * Find a project by its name.
     *
     * @param mixed $name
     * @return mixed
     */
    public function findByProjectName($name);

    /**
     * Find a project by its ProjectId.
     *
     * @param mixed $projectId
     * @return mixed
     */
    public function findByProjectId($projectId);

    /**
     * Delete a project by its ProjectId.
     *
     * @param mixed $projectId
     * @return bool
     */
    public function deleteByProjectId($projectId): bool;

    /**
     * Assign a project to multiple users.
     *
     * @param array $userIds
     * @param string $projectId
     * @param int $$workHours
     * @return mixed
     */
    public function assign(array $userIds, string $projectId, $workHours);

    /**
     * Unassign a project from multiple users.
     *
     * @param array $userIds
     * @param string $projectId
     * @return mixed
     */
    public function unAssign(array $userIds, string $projectId);

    /**
     * get all employee projects by auth
     * @return mixed Projects
     */
    public function employeeProject();

    /**
     * edit a project schedule by id.
     *
     * @param array $projectData
     * @param string $refId
     * @return mixed
     */
    public function scheduleEdit(array $projectData, string $refId);

    /**
     * Check project timelines and send appropriate reminders.
     *
     * This function iterates over user projects, examines project timelines, and sends reminders based on the project duration.
     * - For projects lasting a year or more, it sends quarterly reminders.
     * - For projects lasting a month or more, it sends reminders every 3 days.
     * - For projects lasting less than a month, it sends reminders every 3 days and weekly reminders.
     *
     * Additionally, job scheduling logic for late project reminders is included in comments, uncomment as needed.
     *
     * @return mixed
     */
    function checkProjectTimeLines();

    /**
     * Find a project by its Project Authorization Id.
     *
     * @param mixed $project_id
     * @return mixed
     */
    public function findByAuthId($project_id);


    // Archive operations

    /**
     * Fetch all archived projects.
     *
     * @return mixed
     */
    public function fetchArchives();

    /**
     * Remove a project from the archive by its ProjectId.
     *
     * @param mixed $projectId
     * @return mixed
     */
    public function deleteArchive($projectId);

    /**
     * Search for archived projects by name or code.
     *
     * @param mixed $nameOrCode
     * @return mixed
     */
    public function searchByNameOrCode($nameOrCode);
    
    /**
     * Restore a soft-deleted (archived) project by projectId.
     *
     * @param int $projectId
     * @return bool
     */
    public function archivesRestore($projectId);
    /**
     * Find an archived project by its ProjectId.
     *
     * @param mixed $projectId
     * @return mixed
     */
    public function findArchiveByProjectId($projectId);

    /**
     * Retrieves basic reports for the user.
     * These reports include utilization and contribution metrics.
     * Utilization metrics provide insights into the user's time management,
     * while contribution metrics detail the user's involvement in projects.
     *
     * @return mixed  containing utilization and contribution data for the user.
     */
    public function basicUser();

    /**
     * Retrieves projects reports .
     *
     * @return mixed
     */
    public function projectReports();

    /**
     * Fetch all asigned projects .
     *
     * @param mixed $query
     * @return mixed
     */
    public function utilization();
}
