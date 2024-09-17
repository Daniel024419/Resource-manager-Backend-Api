<?php

use App\Enums\Roles;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\Auth\AuthController;
use App\Http\Controllers\API\V1\AutoAssign\AutoAssignController;
use App\Http\Controllers\API\V1\User\UsersController;
use App\Http\Controllers\API\V1\Client\ClientController;
use App\Http\Controllers\API\V1\Dashboard\DashboardController;
use App\Http\Controllers\API\V1\Skills\SkillsController;
use App\Http\Controllers\API\V1\Project\ProjectController;
use App\Http\Controllers\API\V1\Department\DepartmentController;
use App\Http\Controllers\API\V1\Notification\NotificationController;
use App\Http\Controllers\API\V1\Reports\ReportController;
use App\Http\Controllers\API\V1\Specialization\SpecializationController;
use App\Http\Controllers\API\V1\UserGroup\UserGroupController;
use App\Http\Controllers\API\V1\TimeOff\TimeOffController;
use App\Http\Controllers\API\V1\TimeTracking\TimeTrackingController;

Route::get('/helloworld', function(){
    return "Hello World";
});

// Dashboard
/**
 * Routes for admin/manager dashboard
 * - GET /v1/dashboard/time-off-info: Retrieve a user's upcoming time offs, employees currently on time off, and employees returning from time off.
 * - GET /v1/dashboard/project-info: Retrieve information about projects, including upcoming and active projects.
 * - GET /v1/dashboard/client-info: Retrieve information about clients and related data.
 * - GET /v1/dashboard/time-off-request: Retrieve information about time off requests (all, pending and approved).
 */
Route::controller(DashboardController::class)
    ->middleware(['auth:sanctum'])
    ->prefix("/v1/dashboard")
    ->group(function () {
        Route::get('/time-off-info', 'timeOffInfo');
        Route::get('/project-info', 'projectInfo')->middleware(['AdminManagerAccess']);
        Route::get('/client-info', 'clientInfo')->middleware(['AdminManagerAccess']);
        Route::get('/time-off-request', 'timeOffRequest')->middleware(['AdminManagerAccess']);
    });


//Time Tracking
/**
 * Routes for time tracking:
 *
 * - POST /v1/user/time-tracking/track-time: Track time for a user.
 * - GET /v1/user/time-tracking/time-tracks: Get time tracks for a user.
 * - PUT /v1/user/time-tracking/update-tracked-time/{id}: Update tracked time for a user.
 * - DELETE /v1/user/time-tracking/delete/{id}: Delete tracked time for a user.
 */
Route::controller(TimeTrackingController::class)
    ->middleware(['auth:sanctum'])
    ->prefix("/v1/user/time-tracking")
    ->group(function () {
        Route::post('/track-time', 'trackTime');
        Route::get('/time-tracks', 'getTimeTracks');
        Route::put('/update-tracked-time/{id}', 'updatetrackedTime');
        Route::delete('/delete/{id}', 'deleteTime');
    });



//Auto Assign
/**
 * Routes for auto assigning
 */
Route::controller(AutoAssignController::class)
    ->middleware(['AdminManagerAccess', 'auth:sanctum'])
    ->prefix("/v1")
    ->group(function () {
        Route::post('/auto-assign/{project}', 'autoAssign');
    });

// Dashboard
/**
 * Routes for admin/manager dashboard
 * - GET /v1/dashboard/time-off-info: Retrieve a user's upcoming time offs, employees currently on time off, and employees returning from time off.
 * - GET /v1/dashboard/project-info: Retrieve information about projects, including upcoming and active projects.
 * - GET /v1/dashboard/client-info: Retrieve information about clients and related data.
 * - GET /v1/dashboard/time-off-request: Retrieve information about time off requests (all, pending and approved).
 */
Route::controller(DashboardController::class)
    ->middleware(['auth:sanctum'])
    ->prefix("/v1/dashboard")
    ->group(function () {
        Route::get('/time-off-info', 'timeOffInfo');
        Route::get('/project-info', 'projectInfo')->middleware(['AdminManagerAccess']);
        Route::get('/client-info', 'clientInfo')->middleware(['AdminManagerAccess']);
        Route::get('/time-off-request', 'timeOffRequest')->middleware(['AdminManagerAccess']);
    });



//User Groups

/**
 * Routes for user groups:
 * - POST /v1/users/groups/create-user-group
 * - PUT /v1/users/groups/{refId}/update-user-group
 * - GET /v1/users/groups/{section}-{id}-subgroups
 * - POST /v1/users/groups/{refId}-assign-user-to-subgroup
 * - DELETE /v1/users/groups/{refId}/delete-sub-group
 */
Route::controller(UserGroupController::class)
    ->middleware(['auth:sanctum', 'AdminManagerAccess'])
    ->prefix("/v1/users/groups")
    ->group(function () {
        Route::post('/create-user-group', 'createGroup');
        Route::put('/{refId}/update-user-group', 'updateGroup');
        Route::get('/{section}-{id}-subgroups', 'showUserGroups');
        Route::post('/{refId}/assign-user-to-subgroup', 'assignUserToUserGroup');
        Route::delete('/{refId}/delete-sub-group', 'deleteGroup');
    });


// Time Off Routes
/**
 * Routes for managing user time off requests.
 *
 * These routes are protected by Sanctum authentication.
 * - GET /v1/users/timeoff/people-on-leave: Retrieve a list of people on leave.
 * - POST /v1/users/timeoff/create/time-off-type: Create a new type of time off. (Requires Admin or Manager access)
 * - PUT /v1/users/timeoff/update/time-off-type/{refId}: Update details of a specific time off type. (Requires Admin or Manager access)
 * - DELETE /v1/users/timeoff/delete/time-off-type/{refId}: Delete a specific time off type. (Requires Admin or Manager access)
 * - POST /v1/users/timeoff/book-leave: bookLeave a leave.
 * - GET /v1/users/timeoff/leave-types: Retrieve a list of leave types.
 * - GET /v1/users/timeoff/pending-leaves: Retrieve a list of pending people. (Requires Admin or Manager access)
 * - PUT v1/users/timeoff/leave/{action}/{refId}: Approve or Reject a leave in pending. (Requires Admin or Manager access)
 * - GET v1/users/timeoff/leave-history: Get user booking leave history.
 * - PUT v1/users/timeoff/reassign-leave-request: reassign the approval or rejection of a leave to another manager (Requires Admin access)
 */
Route::controller(TimeOffController::class)
    ->middleware(['auth:sanctum'])
    ->name("users.timeoff.")
    ->prefix("/v1/users/timeoff")
    ->group(function () {
        Route::get('/people-on-leave', 'employeesOnLeave')->name('onLeave');

        Route::post('/create/time-off-type', 'store')
            ->middleware(['AdminManagerAccess']);

        Route::put('/update/time-off-type/{refId}',  'update')
            ->middleware(['AdminManagerAccess'])
            ->name('update');

        Route::delete('/delete/time-off-type/{refId}',  'deleteLeaveType')
            ->middleware(['AdminManagerAccess'])
            ->name('deleteLeaveType');

        Route::post('/book-leave', 'bookLeave')->name('bookLeave');

        Route::get('/leave-types', 'leaveTypes')->name('leaveTypes');

        Route::get('/pending-leaves', 'pendingLeaves')
            // ->middleware(['AdminManagerAccess'])
            ->name('pending.leaves');

        Route::put('/leave/{action}/{refId}', 'manageLeave')
            ->middleware(['AdminManagerAccess'])
            ->name('manage.leave');

        Route::get('/leave-history', 'leaveHistory')->name('leaveHistory');

        Route::put('/reassign-leave-request', 'reassignLeaveRequest')->middleware(['single.guard.access:' . Roles::ADMIN->value]);
    });

// Authentication Routes
/**
 * Routes for user authentication.
 * * - POST /v1/users/login: User login (Unprotected).
 * - POST /v1/users/logout: User logout (requires authenticated user).
 * - GET /v1/users/token/exchange: Token Exchange (Protected by Sanctum).
 */
Route::controller(AuthController::class)
    ->name("users.")
    ->prefix("/v1/users")
    ->group(function () {
        // User Login (Unprotected)
        Route::post('/login', 'login')->name('api.login');
        // User Logout (Protected by Sanctum)
        Route::post('/logout', 'logout')->middleware(['auth:sanctum'])->name('logout');
        // Token Exchange (Protected by Sanctum)
        Route::get('/token/exchange', 'tokenExhange')->middleware(['auth:sanctum'])->name('tokenExhange');
    });

// Unprotected User Routes
/**
 * Routes for updating user information.
 *
 * - PUT /v1/users/update/password: Update user password (Unprotected).
 * - POST /v1/users/send-otp: Send OTP code (Unprotected).
 */
Route::controller(UsersController::class)
    ->name("users.")
    ->prefix("/v1/users")
    ->group(function () {
        // Update user password (Unprotected)
        Route::put('/update/password', 'updatePassword')->middleware(['auth:sanctum'])->name("resetPassword");
        // Send OTP code (Unprotected)
        Route::post('/send-otp', 'sendOTPcode')->name("sendOTPcode");
        //verify otp code (Unprotected)
        Route::post('/verify-otp', 'verifyOTPcode')->name("verifyOTPcode");
    });

/**
 * Routes for users.
 *
 * - POST /v1/users/search: Search users (Protected by Sanctum).
 * - GET /v1/users/fetch: Fetch all users (Protected by Sanctum).
 ** - GET /v1/users/active: Fetch all active accounts (Protected by Sanctum).
 * - GET /v1/users/inactive: Fetch all inactive accounts (Protected by Sanctum).
 * - DELETE /v1/users/delete: Delete user accounts (Protected by Sanctum).
 * - PUT /v1/users/profile/password/update: Update user account password on profile (Protected by Sanctum).
 * - PUT /v1/users/profile/update: Update user profile (Protected by Sanctum).
 * - PUT /v1/users/update/work/specialization: Update user work specialization (Protected by Sanctum).
 * - PUT /v1/users/edit: Edit user/individual profile by admin/manager (Protected by Sanctum).
 * - PUT /v1/users/admin/update/profile: Admin user profile edit by admin (owner) (Protected by Sanctum).
 * - POST /v1/users/archives/store: Store archives users (Protected by Sanctum).
 * - POST /v1/users/archives/restore: restore archives users (Protected by Sanctum).
 * - POST /v1/users/archives/resend/mail resend archives users account invite (Protected by Sanctum).
 * - PUT /v1/users/archives/update: Update archives users (Protected by Sanctum).
 * - GET /v1/users/archives/fetch: Fetch archives users (Protected by Sanctum).
 * - DELETE /v1/users/archives/delete: Delete archives  users (Protected by Sanctum).
 * - POST /v1/users/archives/search: Search archives users (Protected by Sanctum).
 * - GET /v1/users/fetch-all-managers: Fetch all managers
 */
Route::controller(UsersController::class)
    ->name("users.")
    ->prefix("/v1/users")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Search users (Protected by Sanctum)
        Route::post('/search', 'search')->name("search");
        // Fetch all users (Protected by Sanctum)
        Route::get('/fetch', 'fetch')->name("fetch");
        // Fetch all active accounts (Protected by Sanctum)
        Route::get('/active', 'active')->name("active");
        // Fetch all inactive accounts (Protected by Sanctum)
        Route::get('/inactive', 'inactive')->name("inactive");
        // reinvite users (Protected by Sanctum)
        Route::post('/reinvite', 'reInvite')->name("reInvite");
        // Delete user accounts (Protected by Sanctum)
        Route::delete('/delete', 'delete')->name('delete');
        // Update user account password on profile (Protected by Sanctum)
        Route::put('/profile/password/update', 'profileUpdatePassword')->name("profileUpdatePassword");
        // Update user profile (Protected by Sanctum)
        Route::put('/profile/update/', 'updateProfile')->name("updateProfile");
        // Edit user/individual profile by admin/manager (Protected by Sanctum)
        Route::put('/edit', 'edit')->name("edit");
        // Admin user profile edit by admin (owner) (Protected by Sanctum)
        Route::put('/admin/update/profile', 'adminUpdateProfile')->name("adminUpdateProfile");
        // Register user (Protected by Sanctum)
        Route::post('/store', 'store')->name("api.registerUser");
        //fetch all bookable users
        Route::get('/fetch/bookable', 'fetchBookable')->name("fetchBookable");
        // Setup user account (Protected by Sanctum)
        Route::post('/account/setup', 'accountSetup')->name("accountSetup");
        // Set new password (Protected by Sanctum)
        Route::post('/set/new/password', 'NewPassword')->name("NewPassword");
        // Update initial password (Protected by Sanctum)
        Route::put('/update/initial/password', 'updateInitialPassword')->name("updateInitialPassword");

        //archive operations
        Route::get('/archives/fetch', 'archivesFetch')->name("archives.fetch");
        // restore archive users (Protected by Sanctum)
        Route::post('/archives/restore', 'archivesRestore')->name("archives.restore");
        // Delete archive users (Protected by Sanctum)
        Route::delete('/archives/delete', 'archivesDelete')->name("archives.delete");
        // Search archive users (Protected by Sanctum)
        Route::post('/archives/search', 'archivesSearch')->name("archives.search");

        Route::get('/fetch-all-managers', 'fetchAllManagers')->name("fetch.managers");
    });

// Notification Routes
/**
 * Routes for notifications.
 * - GET /v1/users/notifications/fetch: Fetch all notifications (Protected by Sanctum).
 * - POST /v1/users/notifications/mark-all-as-read: Mark all notifications as read (Protected by Sanctum).
 * - POST /v1/users/notifications/mark-notification-as-read: Mark a notifications as read (Protected by Sanctum).
 */
Route::controller(NotificationController::class)
    ->name("users.notifications")
    ->prefix("/v1/users/notifications")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::get('/fetch', 'fetch')->name("fetch");
        Route::post('/mark/all/read', 'markAllAsRead')->name("markAllRead");
        Route::post('/mark/one/read', 'markOneAsRead')->name("markOneAsRead");
    });


// Specialization Routes
/**
 * Routes for managing specializations.
 *
 * - POST /v1/specialization/store: Store specialization (Protected by Sanctum).
 * - PUT /v1/specialization/update: Update specialization (Protected by Sanctum).
 * - GET /v1/specialization/fetch: Fetch specialization (Protected by Sanctum).
 * - DELETE /v1/specialization/delete: Delete specialization (Protected by Sanctum).
 * - GET /v1/specialization/{id}/users: Fined a single specialization (Protected by Sanctum and AdminManger Guard)
 */
Route::controller(SpecializationController::class)
    ->name("specialization.")
    ->prefix("/v1/specialization")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Store specialization (Protected by Sanctum)
        Route::post('/store', 'store')->name("store");
        // Update specialization (Protected by Sanctum)
        Route::put('/update', 'update')->name("update");
        // Fetch specialization (Protected by Sanctum)
        Route::get('/fetch', 'fetch')->name("fetch");
        // Delete specialization (Protected by Sanctum)
        Route::delete('/delete', 'delete')->name("delete");
        // Find a specilization
        Route::get('/{specialization}/users', 'getASpecilization')->middleware(['AdminManagerAccess'])->name("getASpecilization");
    });

// Department Routes
/**
 * Routes for managing departments.
 *
 * - POST /v1/department/store: Store department (Protected by Sanctum).
 * - PUT /v1/department/update: Update department (Protected by Sanctum).
 * - GET /v1/department/fetch: Fetch department (Protected by Sanctum).
 * - DELETE /v1/department/delete: Delete department (Protected by Sanctum).
 */
Route::controller(DepartmentController::class)
    ->name("department.")
    ->prefix("/v1/department")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Store department (Protected by Sanctum)
        Route::post('/store', 'store')->name("store");
        // Update department (Protected by Sanctum)
        Route::put('/update', 'update')->name("update");
        // Fetch department (Protected by Sanctum)
        Route::get('/fetch', 'fetch')->name("fetch");
        // Delete department (Protected by Sanctum)
        Route::delete('/delete', 'delete')->name("delete");
    });

// Skills Routes
/**
 * Routes for managing skillss.
 *
 * - POST /v1/skills/store: Store skills (Protected by Sanctum).
 * - PUT /v1/skills/update: Update skills (Protected by Sanctum).
 * - GET /v1/skills/fetch: Fetch skills (Protected by Sanctum
 * - GET /v1/skills/fetch/by/auth: Fetch skills (Protected by Sanctum).
 * - DELETE /v1/skills/delete: Delete skills (Protected by Sanctum).
 */
Route::controller(SkillsController::class)
    ->name("skills.")
    ->prefix("/v1/skills")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Store users skills (Protected by Sanctum)
        Route::post('/store', 'store')->name("store");
        // Update skills (Protected by Sanctum)
        Route::put('/update', 'update')->name("update");
        // Fetch skills (Protected by Sanctum)
        Route::get('/fetch', 'fetch')->name("fetch");
        //fetch skills by auth (Protected by sanctum)
        Route::get('/fetch/by/auth', 'fetchByAuth')->name("fetchByAuth");

        // Delete skills (Protected by Sanctum)
        Route::delete('/delete', 'delete')->name("delete");
    });


// Client Routes
/**
 * Routes for managing clients.
 *
 * - POST /v1/client/store: Store client (Protected by Sanctum).
 * - PUT /v1/client/update: Update client (Protected by Sanctum).
 * - GET /v1/client/fetch: Fetch client (Protected by Sanctum).
 * - DELETE /v1/client/delete: Delete client (Protected by Sanctum).
 * - POST /v1/client/search: Search clients (Protected by Sanctum).
 * - POST /v1/client/archives/store: Store archives client (Protected by Sanctum).
 * - POST /v1/client/archives/restore: restore archives clients (Protected by Sanctum).
 * - PUT /v1/client/archives/update: Update archives client (Protected by Sanctum).
 * - GET /v1/client/archives/fetch: Fetch archives client (Protected by Sanctum).
 * - DELETE /v1/client/archives/delete: Delete archives  client (Protected by Sanctum).
 * - POST /v1/client/archives/search: Search archives clients (Protected by Sanctum).
 */
Route::controller(ClientController::class)
    ->name("client.")
    ->prefix("/v1/client")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Store client (Protected by Sanctum)
        Route::post('/store', 'store')->name("store");
        // Update client (Protected by Sanctum)
        Route::put('/update', 'update')->name("update");
        // Fetch client (Protected by Sanctum)
        Route::get('/fetch', 'fetch')->name("fetch");
        // Delete client (Protected by Sanctum)
        Route::delete('/delete', 'delete')->name("delete");
        // Search clients (Protected by Sanctum)
        Route::post('/search', 'search')->name("search");
        // find clients by clientId (Protected by Sanctum)
        Route::post('/find/clientId', 'findById')->name("findById");

        //archives operations
        // restore archive client (Protected by Sanctum)
        Route::post('/archives/restore', 'archivesRestore')->name("archives.restore");
        // Fetch archive client (Protected by Sanctum)
        Route::get('/archives/fetch', 'archivesFetch')->name("archives.fetch");
        // Delete archive client (Protected by Sanctum)
        Route::delete('/archives/delete', 'archivesDelete')->name("archives.delete");
        // Search archive clients (Protected by Sanctum)
        Route::post('/archives/search', 'archivesSearch')->name("archives.search");
    });


// Project Routes
/**
 * Routes for managing projects.
 *
 * - POST /v1/project/store: Store project (Protected by Sanctum).
 * - PUT /v1/project/update: Update project (Protected by Sanctum).
 * - GET /v1/project/fetch: Fetch project (Protected by Sanctum).
 * - DELETE /v1/project/delete: Delete project (Protected by Sanctum).
 * - POST /v1/project/search: Search projects (Protected by Sanctum).
 * - POST /v1/project/find/projectId: Find project by projectId (Protected by Sanctum).
 * - POST /v1/project/assign/store: Assign users to project (Protected by Sanctum).
 * - POST /v1/project/unassign: Unassign user from project (Protected by Sanctum).
 * - PUT /v1/project/schedule/edit: Edit schedule of users on project (Protected by Sanctum).
 * - GET /v1/project/fetch/employee-project: Fetch all employee projects by auth (Protected by Sanctum).
 * - POST /v1/project/archives/restore: Restore archived project (Protected by Sanctum).
 * - PUT /v1/project/archives/update: Update archived project (Protected by Sanctum).
 * - GET /v1/project/archives/fetch: Fetch archived project (Protected by Sanctum).
 * - DELETE /v1/project/archives/delete: Delete archived project (Protected by Sanctum).
 * - POST /v1/project/archives/search: Search archived projects (Protected by Sanctum).
 * - POST /v1/project/user-assigned-projects: Get all projects an employeee is assigned to (Protected by Sanctum).
 */

Route::controller(ProjectController::class)
    ->name("project.")
    ->prefix("/v1/project")
    ->middleware(['auth:sanctum'])
    ->group(function () {
        // Store project (Protected by Sanctum)
        Route::post('/store', 'store')->name("store");
        // Update project (Protected by Sanctum)
        Route::put('/update', 'update')->name("update");
        // Fetch project (Protected by Sanctum)
        Route::get('/fetch', 'fetch')->name("fetch");
        // Delete project (Protected by Sanctum)
        Route::delete('/delete', 'delete')->name("delete");
        // Search projects by name (Protected by Sanctum)
        Route::post('/search', 'search')->name("search");
        // find project by projectId (Protected by Sanctum)
        Route::post('/find/projectId', 'findById')->name("findById");
        // assign users to project (Protected by Sanctum)
        Route::post('/assign', 'assign')->name("assign");
        // restore assigned user from project (Protected by Sanctum)
        Route::post('/unassign', 'unAssign')->name("unAssign");
        // Fetch all empoyee projects by auth
        Route::get('/fetch/employee-projects', 'employeeProject')->name("employeeProject");
        // edit schedule of users on project (Protected by Sanctum)
        Route::put('/schedule/edit', 'scheduleEdit')->name("scheduleEdit");
        //archieve operations
        // Store archive project (Protected by Sanctum)
        Route::post('/archives/restore', 'archivesRestore')->name("archives.eestore");
        // Fetch archive  project (Protected by Sanctum)
        Route::get('/archives/fetch', 'archivesFetch')->name("archives.fetch");
        // Delete archive  project (Protected by Sanctum)
        Route::delete('/archives/delete', 'archivesDelete')->name("archives.delete");
        // Search archive project (Protected by Sanctum)
        Route::post('/archives/search', 'archiveseSarch')->name("archivese.sarch");
        // Get all projects an employeee is assigned to
        Route::get('/user-assigned-projects', 'userAssignedProject')->name("assigned.user.project");
        //extend project timeline (Protected by sanctum)
        Route::put('/extend-timeline', 'extendTimeLine')->middleware(['AdminManagerAccess'])->name("extendTimeLine");
        //fetch all project extentions
        Route::get('/extensions', 'extentions')->middleware(['AdminManagerAccess'])->name("extentions");
        // Get all projects an employeee is assigned to
        Route::get('/user-assigned-projects', 'userAssignedProject')->name("assigned.user.project");
    });



/*
* Report Routes
*
* These routes are responsible for generating various reports.
* They are grouped under the /v1/reports prefix and require authentication.
*
* -GET /basic-user: Generates a basic user report.
* -GET /clients: Generates a report on client data.
* -GET /utilization: Generates a report on resource utilization.
*
*/
Route::controller(ReportController::class)
    ->name("reports")
    ->prefix("/v1/reports")
    ->middleware(['auth:sanctum'])
    ->group(function () {

        Route::get('/basic-user', 'basicUser')->name("basicUser");
        Route::get('/clients', 'clients') ->middleware(['AdminManagerAccess'])->name("clients");
        Route::get('/projects', 'projects') ->middleware(['AdminManagerAccess'])->name("projects");
        Route::get('/time-off', 'timeOff') ->middleware(['AdminManagerAccess'])->name("timeOff");
        Route::get('/utilization', 'utilization') ->middleware(['AdminManagerAccess'])->name("utilization");
    });

