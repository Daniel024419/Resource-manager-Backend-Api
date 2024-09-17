<?php

namespace App\Repository\V1\Users;

interface UserInterfaceRepository
{

    /**
     * fetch all managers
     *
     */
    public function fetchAllManagers();
    
    /**
     *@return array
     */
    public function fetchAllUsers($query);

    /**
     * find user by id
     * @param int $id
     * @return object
     */
    public function find($id);

    /**
     *@retrun save $users
     * @return array< int , strin>
     */
    public function save(array $userData);

    /**
     *@retrun user by email
     *@param $id , $username , $email
     * @return string< int , strin>
     */

    public function findByEmail(string $email);

    /**
     *@retrun user
     *@param $search_param
     * @return string< int , strin>
     */
    public function findByParam(string $search_param);

    /**
     *@retrun user
     *@param $userData
     * @return string< int , strin>
     */

    public function updatePassword(string $email, string $password);

    /**
     *@var email $Password
     * @param array< int , strin>
     */
    public function updateInitialPassword(string $email, string $password);

    /**
     *@return boolean
     * @param array < int , string
     */
    // date  user email with an array of data
    public function updateEmailByUserId(int $userId, string $email): bool;

    /**
     *@var $userId
     * @param < strin>
     */
    public function findById(string $userId);

    /**
     * Delete a user by email.
     *
     * @param string $email The email or username of the user to delete.
     * @return true|null The deleted user, or null if the user could not be found.
     */
    public function deleteByemail(string $email): bool;


    /**
     * Delete incomplete user by email.
     *
     * @param string $email The email or username of the user to delete
     * @return bool True on success, false on failure
     */
    public function deleteIncomplteAccountByemail(string $email): bool;

    /**
     * Get all active users,
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function active();

    /**
     * Get all incomplete users,
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function inactive();

    // Archive operations

    /**
     * Fetch all archived Users.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch();

    /**
     * Soft delete (archive) a User by UserId.
     *
     * @param int $UserId
     * @return bool
     */
    public function store($email);

    /**
     * Restore a soft-deleted (archived) User by UserId.
     *
     * @param int $UserId
     * @return bool
     */
    public function restoreArchive($email);

    /**
     * Permanently delete a soft-deleted (archived) User by UserId.
     *
     * @param int $UserId
     * @return bool
     */
    public function deleteArchive($email);

    /**
     * Search for archived Users by name or User code.
     *
     * @param string $nameOrCode
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function archivesSearch($search_param);

    /**
     * Search for archived Users by email
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function searchByEmail($email);

    /**
     * Retrieve reports of employee's time off.
     *
     * @return mixed
     */
    public function timeOffRequest();
}