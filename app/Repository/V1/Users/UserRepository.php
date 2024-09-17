<?php

namespace App\Repository\V1\Users;

use App\Enums\Roles;
use App\Models\V1\Client\Client;
use Exception;
use Carbon\Carbon;
use App\Models\V1\User\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Employee\Employee;
use Illuminate\Support\Facades\Hash;
use App\Models\V1\Project\EmployeeProject;
use App\Models\V1\Project\Project;
use App\Models\V1\Project\ProjectHistory;
use App\Service\V1\Employee\EmployeeService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepository implements UserInterfaceRepository
{

    public function fetchAllManagers()
    {
        try {
            $today = Carbon::today();
            $managers = User::whereHas('employee', function ($query) {
                $query->where('roleId', Roles::getRoleIdByValue(Roles::MGT->value));
            })->whereDoesntHave('leaveRequests', function ($query) use ($today) {
                $query->where('status', 'approved')->whereDate('startDate', '<=', $today)
                    ->whereDate('endDate', '>=', $today);
            })
                ->with('employee')
                ->orderBy('email')
                ->get();

            return $managers;
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid input',
            ];
        }
    }
    /**
     * Fetch all users from the database.
     *
     * @return array
     * @throws ModelNotFoundException
     */
    public function fetchAllUsers($query)
    {
        try {
            // Fetch all users from the 'users' table
            if (!empty($query)) {
                $users = User::take($query)->orderBy('email', 'asc')->get();
            }
            $users = User::orderBy('email', 'asc')->get();

            $usersOverView = $this->usersOverView($users);

            return [
               'usersOverView' =>$usersOverView,
               'users' => $users, 
            ];
        } catch (ModelNotFoundException $e) {
            // Throw an exception if no users are found
            throw new ModelNotFoundException();
        }
    }

    /** 
    *generate user overview within a specific month
    *@return array
    */
    private function usersOverView($users){

        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $percentageCurrentMonth = 0;
        $percentagePreviousMonth = 0;
        $type = "";

        $currentMonthsCounts = $users->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
        $previousMonthsCounts = $users->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $totalUsersCount = $users->count();
        if ($totalUsersCount > 0) {
            $percentageCurrentMonth = ($currentMonthsCounts / $totalUsersCount) * 100;
            $percentagePreviousMonth = ($previousMonthsCounts / $totalUsersCount) * 100;
        }
        if ($currentMonthsCounts > $previousMonthsCounts) {
            $type = "Increased";
        } else if ($currentMonthsCounts == $previousMonthsCounts) {
            $type = "Stable";
        } else {
            $type = "Decreased";
        }

        return [
            'totalNumberOfUsers' => $totalUsersCount,
            'type' => $type,
            'percentage' => round(abs($percentageCurrentMonth - $percentagePreviousMonth), 1),
        ];
    }

    /**
     * Save a new user with the provided data array.
     *
     * @param array $userData An array containing user data
     * @return User|null The newly created user instance or null on failure
     */
    public function save(array $userData)
    {
        try {
            DB::beginTransaction();
            // Create a new User instance and save it to the 'users' table
            $user = User::create($userData);
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            // Roll back the transaction in case of an exception
            DB::rollBack();

            return null;
        }
    }

    /**
     * find user by id
     * @param int $id
     * @return object
     */
    public function find($id)
    {
        return User::find($id);
    }
    /**
     * Find a user by their email or username.
     *
     * @param string $email The email or username to search for
     * @return User|null The user instance or null if not found
     * @throws ModelNotFoundException
     */
    public function findByEmail(string $email)
    {
        try {
            // Find a user by email or username, and eager load the 'employee' relationship
            $user = User::where("email", "=", $email)
                ->with('employee')
                ->first();

            return $user;
        } catch (ModelNotFoundException $e) {
            // Throw an exception if no user is found
            throw new ModelNotFoundException();
        }
    }

    /**
     * Search for users based on a search parameter.
     *
     * @param string $search_param The parameter to search for in users and employees
     * @return \Illuminate\Database\Eloquent\Collection|array The collection of matched users and employees
     * @throws ModelNotFoundException
     */
    public function findByParam(string $search_param)
    {
        try {
            // ILIKE case-insensitive search for employees based on first name, last name, and phone number
            $employees = Employee::where('firstName', 'LIKE', "%$search_param%")
                ->orWhere('lastName', 'LIKE', "%$search_param%")
                ->orWhere('phoneNumber', 'LIKE', "%$search_param%")
                ->orWhere('userId', 'LIKE', "%$search_param%")
                ->get();
            // Extract user IDs from employees collection
            $userId = $employees->pluck('userId')->toArray();
            // Find users based on email and user ID
            $users = User::whereIn('id', $userId)
                ->orWhere('email', 'LIKE', "%$search_param%")
                ->with('employee') // Eager load the 'employee' relationship
                ->get();
            // Combine results using union
            $combinedResults = $users->isNotEmpty() ? $users->union($employees) : $employees;
            return $combinedResults;
        } catch (ModelNotFoundException $e) {
            // Throw an exception if no users or employees are found
            throw new ModelNotFoundException();
        }
    }

    /**
     * Update the initial user password identified by email.
     *
     * @param string $email The email of the user
     * @param string $password The new hashed password
     * @return mixed The updated user instance or null on failure
     * @throws ModelNotFoundException
     */
    public function updateInitialPassword(string $email, string $password)
    {
        try {
            DB::beginTransaction();
            // Hash the password before updating
            $hashedPassword = Hash::make($password);
            // Update the user's hashed password and set 'changePassword' to false
            $user = User::where('email', $email)->update([
                'password' => $hashedPassword,
            ]);
            DB::commit();
            return $user;
        } catch (ModelNotFoundException $e) {
            // Roll back the transaction in case of an exception
            DB::rollBack();
            // Throw an exception if the user is not found
            throw new ModelNotFoundException();
        }
    }

    /**
     * Update the user password identified by email.
     *
     * @param string $email The email of the user
     * @param string $password The new hashed password
     * @return mixed The updated user instance or null on failure
     * @throws ModelNotFoundException
     */
    public function updatePassword(string $email, string $password)
    {
        try {
            DB::beginTransaction();

            // Hash the password before updating
            $hashedPassword = Hash::make($password);

            // Update the user's hashed password and set 'updated_at' to the current date
            $user = User::where('email', '=', $email)->update([
                'password' => $hashedPassword,
                'updated_at' => now()->toDateTimeString()
            ]);

            DB::commit();
            return $user;
        } catch (ModelNotFoundException $e) {
            // Roll back the transaction in case of an exception
            DB::rollBack();
            // Throw an exception if the user is not found
            throw new ModelNotFoundException();
        }
    }

    /**
     * Update the email of a user identified by user ID.
     *
     * @param int $userId The user ID
     * @param string $email The new email address
     * @return bool True on success, false on failure
     */
    public function updateEmailByUserId(int $userId, string $email): bool
    {
        try {
            DB::beginTransaction();
            // Update the user's email
            $user = User::where('id', '=', $userId)->update(['email' => $email]);
            DB::commit();
            return true;
        } catch (ModelNotFoundException $e) {
            // Roll back the transaction in case of an exception
            DB::rollBack();

            return false;
        }
    }

    /**
     * Search for a user by ID.
     *
     * @param string $userId The ID of the user to search for
     * @return User|array|null The user instance or null if not found
     */
    public function findById(string $userId)
    {
        try {
            // Find a user by ID and eager load the 'employee' relationship
            return User::where('id', '=', $userId)->with('employee')->first();
        } catch (ModelNotFoundException $e) {
            // Return null or an empty array based on your use case
            return null;
        }
    }

    /**
     * Delete a user by email.
     *
     * @param string $email The email or username of the user to delete
     * @return bool True on success, false on failure
     */
    public function deleteByemail(string $email): bool
    {
        try {
            DB::beginTransaction();

            // Delete the user by email, including the associated 'employee' record
            $user = User::where("email", "=", $email)->with('employee')->first();
            $employeeId = $user->employee->id;
            EmployeeProject::where('employee_id', $employeeId)->delete();
            $user->delete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            // Roll back the transaction in case of an exception
            DB::rollBack();
            return false;
        }
    }

    /**
     * Delete incomplete user by email.
     *
     * @param string $email The email or username of the user to delete
     * @return bool True on success, false on failure
     */
    public function deleteIncomplteAccountByemail(string $email): bool
    {
        try {
            DB::beginTransaction();

            // Delete the user by email, including the associated 'employee' record
            User::where("email", "=", $email)->with('employee')->forceDelete();

            DB::commit();
            return true;
        } catch (Exception $e) {
            // Roll back the transaction in case of an exception
            DB::rollBack();
            return false;
        }
    }

    /**
     * Get all active users,
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function active()
    {
        try {
            $users = User::whereHas('employee', function ($query) {
                $query->whereNotNull('firstName')
                    ->whereNotNull('lastName')
                    ->whereNotNull('phoneNumber');
            })
                ->whereNotNull('password')
                ->orderBy('email', 'asc')->get();

            return $users;
        } catch (ModelNotFoundException $e) {
            // Return an empty array if no archived users are found
            return [];
        }
    }

    /**
     * Get all incomplete users,
     *
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function inactive()
    {
        try {
            $users =  User::whereHas('employee', function ($query) {
                $query->whereNull('firstName')
                    ->whereNull('lastName')
                    ->whereNull('phoneNumber');
            })
                ->whereNull('password')
                ->orderBy('email', 'asc')->get();


            return $users;
        } catch (ModelNotFoundException $e) {
            // Return an empty array if no archived users are found
            return [];
        }
    }


    // Archive operations
    /**
     * Fetch all archived Users.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch()
    {
        try {
            // Fetch all archived Users (including soft-deleted ones)
            $archivedUsers = User::onlyTrashed()->orderBy('email', 'asc')->get();
            return $archivedUsers;
        } catch (Exception $e) {
            // Handle exceptions and return null
            return null;
        }
    }

    /**
     * Soft delete (archive) a User by UserId.
     *
     * @param int $UserId
     * @return bool
     */
    public function store($email)
    {
        try {
            // Soft delete (archive) a User by UserId
            User::where('email', '=', $email)->delete();
            return true;
        } catch (Exception $e) {
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Restore a soft-deleted (archived) User by UserId.
     *
     * @param int $UserId
     * @return bool
     */
    public function restoreArchive($email)
    {
        try {
            // Restore a soft-deleted (archived) User by UserId
            $user = User::withTrashed()->where('email', $email)->with('employee')->first();

            // Restore the user
            $user->restore();
            Employee::where('userId', $user->id)->restore();

            $employeeProjects = EmployeeProject::withTrashed()
                ->where('employee_id', $user->employee->id)
                ->with('project')
                ->get();

            foreach ($employeeProjects as $employeeProject) {
                $projectEndDate = Carbon::parse($employeeProject->project->endDate);

                // Check if the project's end date has not passed
                if ($projectEndDate->isFuture()) {
                    // Restore the assigned projects for the employee
                    EmployeeProject::withTrashed()->where('employee_id', $user->employee->id)
                        ->where('project_id', $employeeProject->project->id)->restore();
                }
            }

            return true;
        } catch (Exception $e) {
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Permanently delete a soft-deleted (archived) User by UserId.
     *
     * @param int $UserId
     * @return bool
     */
    public function deleteArchive($email)
    {
        try {
            // Permanently delete a soft-deleted (archived) User by UserId
            $user = User::withTrashed()->where('email', $email)->with('employee')->first();
            if(!$user){

                return false;
            }
            Employee::where('userId', $user->id)->forceDelete();
            //remove user permanently
            $user->forceDelete();

            // delete the assigned projects for the employee
            EmployeeProject::withTrashed()->where('employee_id', $user->employee->id)->forceDelete();

            return true;
        } catch (Exception $e) {
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Search for archived Users by name or User code.
     *
     * @param string $nameOrCode
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function archivesSearch($search_param)
    {
        try {
            // Search for archived Users by name or User code
            $archivedUsers = User::withTrashed()->where('email', 'LIKE', '%' . $search_param . '%')->get();
            return $archivedUsers;
        } catch (Exception $e) {
            // Handle exceptions and return an empty array
            return [];
        }
    }

    /**
     * Search for archived Users by email
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function searchByEmail($email)
    {
        try {
            // Search for archived Users by name or User code
            $archivedUsers = User::withTrashed()->where('email', '=', $email)->get();
            return $archivedUsers;
        } catch (Exception $e) {
            // Handle exceptions and return an empty array
            return [];
        }
    }
    /**
     * Retrieve reports of employee's time off.
     *
     * @return mixed
     */
    public function timeOffRequest()
    {
        try {

            $users = User::with(['employee', 'leaveRequests.typeDetail'])
                ->whereHas('leaveRequests')
                ->withTrashed()
                ->get();

            return $users;
        } catch (Exception $e) {

            return null;
        }
    }
}