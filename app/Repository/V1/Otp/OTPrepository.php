<?php

namespace App\Repository\V1\Otp;

use App\Models\V1\Otp\Otp;
use App\Models\V1\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class OTPrepository implements OTPRepositoryinterface
{

    /**
     * Save a new OTP record to the database
     * @param array $OTPData
     * @return Otp|ModelNotFoundException
     */
    function save(array $OTPData): Otp|ModelNotFoundException
    {
        try {
            DB::beginTransaction();
            Log::info($OTPData);
            $OTP = Otp::create($OTPData);
            DB::commit();
            return $OTP;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            Log::info($e);
            return new ModelNotFoundException();
        }
    }

    /**
     * Find an OTP record by the user's ID
     * @param string $userId
     * @return Otp|ModelNotFoundException
     */
    public function findByUserId(string $user_id)
    {
        try {
            $response = Otp::where('user_id', '=', $user_id)->first();
            return $response;
        } catch (ModelNotFoundException $e) {
            return null;
        }
    }

    /**
     * Delete an OTP record by the user's ID
     * @param string $userId
     * @return int
     */
    public function deleteByUserId(string $user_id)
    {
        try {
            DB::beginTransaction();
            $response = Otp::where('user_id', $user_id)->delete();
            DB::commit();
            return $response;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return new ModelNotFoundException();
        }
    }

    /**
     * Find a user by their email or username
     *
     * @param string $email The email or username of the user to find
     * @return User|ModelNotFoundException If the user is found, returns the user object. If the user is not found, throws a ModelNotFoundException
     */
    public function findByEmail(string $email)
    {
        try {
            // Query the database for the user
            $user = User::where("email", "=", $email)
                ->with('employee')
                ->first();
            return $user;
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }
}