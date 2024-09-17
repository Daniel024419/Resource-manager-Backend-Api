<?php

namespace App\Service\V1\Users;


interface UserInterfaceService
{
    /**
     * Fetch all managers.
     *
     * @return array An array containing 'users' and 'status'.
     */
    public function fetchAllManagers(): array;

    /**
     *fetch all active accounts
     */
    public function active(): array;
    /**
     * fetch all users
     *
     * @return mixed The user record, or a ModelNotFoundException if no user was found
     */

    /**
     * Fetch all inactive accounts
     *
     * @return array An array containing 'users' and 'status'.
     */
    public function inactive(): array;
    /**
     * fetch all users
     *
     * @return mixed The user record, or a ModelNotFoundException if no user was found
     */

    public function fetchAllUsers($query): array;

    /**
     * Save a new user
     *
     * @param Request $request
     * @return array
     */
    function save($request): array;

    /**
     * Helper method to save notification data for a new user invitation.
     *
     * @param mixed $authUser  - The authenticated user who initiated the invitation.
     * @param array $employee  - The employee data associated with the new user.
     *
     * @return void
     */
    public function saveNotificationData($authUser, $employee): void;

    /**
     * Helper method to save notification data for a new user invitation.
     *
     * @param mixed $authUser  - The authenticated user who initiated the invitation.
     * @param array $employee  - The employee data associated with the new user.
     *
     * @return void
     */
    public function deleteIncompleteAccounts($user): void;

    /**
     *@retrun $user by email
     * @return string< int , strin>
     */

    function findByEmail(string $email);

    /**
     *@data $userPassword
     * @param mixed< string , strin>
     */
    function updatePassword(string $email, string $password) : mixed;


    /**
     *@data $userId
     * @param < string >
     * @return mixed
     */

    function findById(string $userId): mixed;

    /**
     * Update the password of a user by email
     *
     * @param string $email The email of the user
     * @param string $password The password to be updated
     * @return mixed The updated user record
     */
    public function updatePasswordByEmail(string $email, string $password);


    /**
     * Search service
     *
     * @param $safe_search_param
     * @return array
     */
    public function search($safe_search_param);

    /**
     * Delete a user.
     *
     * @param mixed $request
     * @return array
     */
    public function deleteUser($request): array;

    /**
     * Send a reminder to complete account setup for a user.
     *
     * @param Request $request - The HTTP request containing user data.
     *
     * @return array - An array containing the result of the operation, including status, message, and data.
     */
    public function reInviteUsers($request): array;


     //archive operations
    /**
     * fetch all archived projects
     * @return array
     */
    public function archivesFetch(): array;

    /**
     * unarchive User
     * @param UserIdRequest $request
     * @var $request
     */
    public function archivesRestore( $request);


    /**
     * delete archived Users
     * @param Request $request
     * @var $UserIdRequest $request
     * @return array
     */
    public function archivesDelete( $request);


    /**
     * search archived Users
     * @param UserNameRequest $request
     * @var $request , $UserId
     * @return array
     */
    public function archivesSearch( $request);

}