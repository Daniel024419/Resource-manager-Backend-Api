<?php

namespace App\Repository\V1\Auth;



interface AuthInterfaceRepository
{
    /**
     *@retrun user by email
     *@param  $email
     * @return string< int , strin>
     */
    public function findByEmail(string $email);

    /**
     *@return array $dated user
     * @param array < int , string
     */
    // Save a new user with an array of data
    public function updatePassword(string $email, string $password);
}
