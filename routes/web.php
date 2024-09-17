<?php

use App\Enums\UserGroup;
use App\Models\V1\Department\Department;
use App\Models\V1\skill\Skill;
use App\Models\V1\Specialization\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});



Route::get('/temp-mail', function () {
    return view('vendor.notifications.email');
});

Route::get('/reset-mail', function () {
    return view('emails.reset_otp');
});

Route::get('/invite-mail', function () {
    return view('emails.account_setup_invitation');
});

Route::get('/new-project-deadline', function () {
    return view('emails.project_deadline_notification');
});

Route::get('/new-project-mail', function () {
    return view('emails.new_project_notification');
});


Route::get('/location', function (Request $request) {
    $host = $request->host();
    $hosthttp = $request->ip();
    $scheme = $request->schemeAndHttpHost();

    return response()->json([$host, $hosthttp, $scheme]);
});

Route::get('/model-info', function () {
    $searchTerm = 'scrapper'; // Search term
    
    $skills = Skill::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])->get();
    $employeeIds = [];

    foreach ($skills as $skill) {
        $employeeIds[] = $skill->employee_id;
    }

    return response()->json(['employee_ids' => $employeeIds]);
});

// Catch-all route
Route::fallback(function () {
    abort(404, 'Page not found');
});
