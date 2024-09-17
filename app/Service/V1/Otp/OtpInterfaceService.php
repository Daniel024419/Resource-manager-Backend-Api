<?php

namespace App\Service\V1\Otp;

interface OtpInterfaceService
{

    /**
     *@retrun saved $otp
     * @return array< int, strin>
     */
    function save(array $OTPData);

    /**
     * verify otp code
     *
     * @param mixed $request
     * @return array response
     */
    public function verifyCode($request): array;

    /**
     * Delete OTP by user id
     *
     * @param string $user_id
     * @return int
     */
    public function deleteByUserId(string $user_id): int;

    /**
     * send otp notification after user is found as valid user
     *
     * @param string $email
     * @return array
     */
    public function sendOTP(string $email): array;
}
