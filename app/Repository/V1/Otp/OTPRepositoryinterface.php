<?php

namespace App\Repository\V1\Otp;

interface OTPRepositoryinterface
{
    /**
     *@retrun save $otp
     * @return array< int, strin>
     */
    function save(array $OTPData);

    /**
     *find find $otp
     * @return array < int, strin>
     */
    public function findByUserId(string $user_id);

    /**
     * delete $otp
     * @return int
     */
    public function deleteByUserId(string $user_id);

    /**
     * Find a user by their email or username
     *
     * @param string $email The email or username of the user to find
     * @return User|ModelNotFoundException If the user is found, returns the user object. If the user is not found, throws a ModelNotFoundException
     */
    public function findByEmail(string $email);
}