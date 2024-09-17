<?php

namespace App\Service\V1\Auth;

/**
 * Interface AuthInterfaceService
 * @package App\Service\V1\Auth
 */
interface AuthInterfaceService
{
    /**
     * Find a user by email or username.
     *
     * @param string $email
     * @return array<int, string>
     */
    public function findByEmail(string $email);

    /**
     * Logout a user.
     *
     * @param $request
     * @return array
     */
    public function logout($request): array;

    /**
     * Authenticate a user with token.
     *
     * @param $request
     * @return array
     * @throws AuthenticationException
     */
    public function tokenExchange($request): array;

    /**
     * Authenticate a user and exchange token for notification.
     *
     * @return array
     * @throws AuthenticationException
     */
    public function tokenExchangeForNotification(): array;

    /**
     * Authenticate a user with the provided credentials.
     *
     * @param array $credentials
     * @param $request
     * @return array
     * @throws ModelNotFoundException
     */
    public function authenticateUser(array $credentials, $request): array;

    /**
     * Authenticate a user on password change.
     *
     * @param $request
     * @return array
     * @throws ModelNotFoundException
     */
    public function AuthUserOnPasswordChange($request): array;

    /**
     * Save a new password on account setup.
     *
     * @param $request
     * @return array
     * @throws ModelNotFoundException
     */
    public function savePasswordOnAcccountSetUp($request): array;

     /**
     * change profile seetings password
     *
     * @param array $credentials
     * @return array
     * @throws ModelNotFoundException
     */
    public function profileUpdatePassword($request): array;

    /**
     * Change admin's initial password.
     * Authenticate a user with the provided credentials.
     *
     * @param $request
     * @return array
     * @throws ModelNotFoundException
     */
    public function AuthUserOnInitialPasswordChange($request): array;
}