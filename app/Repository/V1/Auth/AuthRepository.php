<?php


namespace App\Repository\V1\Auth;

use App\Models\V1\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Employee\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthRepository implements AuthInterfaceRepository
{

    /**
     * Find a user by their email or username
     *
     * @param string $email
     * @return User
     */
    public function findByEmail(string $email): ?User
    {

        try {

            //check if user is archived
            $checkAcrhive =  User::onlyTrashed()->where("email", $email)->where('deleted_at', '<>', null)->first();
            if ($checkAcrhive) {
                return $checkAcrhive;
            }
            // Query the database for a user with the given email or username
            $user = User::where("email", $email)
                ->with('employee') // Eager load the 'employee' relationship
                ->first();
            // Return the user if found, or throw a ModelNotFoundException
            return $user;
        } catch (ModelNotFoundException $e) {
            // Handle ModelNotFoundException if necessary
            return new ModelNotFoundException();
        }
    }

    /**
     * Update the user's password
     *
     * @param string $email The user's email address
     * @param string $password The <PASSWORD>
     * @return bool Returns true if the password was updated, false otherwise
     */
    public function updatePassword(string $email, string $password): bool
    {
        try {

            DB::beginTransaction();
            // Hash the password before updating
            $hashedPassword = Hash::make($password);

            // Find User instance and update the hashed password
            $user = User::where('email', '=', $email)->update([
                'password' => $hashedPassword,
            ]);

            DB::commit();

            return $user;
        } catch (ModelNotFoundException $e) {

            DB::rollBack();
            // Handle ModelNotFoundException if necessary
            return false;
        }
    }
}
