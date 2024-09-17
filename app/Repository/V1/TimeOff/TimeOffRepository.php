<?php

namespace App\Repository\V1\TimeOff;

use App\Enums\Roles;
use App\Models\V1\Holiday\Holiday;
use App\Models\V1\TimeOff\TimeOffRequests;
use App\Models\V1\TimeOff\TimeOffType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class TimeOffRepository implements TimeOffRepositoryInterface
{


    /**
     * Reassign a leave request to be managed by another.
     *
     * @return mixed
     */
    public function reassignLeaveRequest(array $data)
    {
        try {
            DB::beginTransaction();

            $reassign = TimeOffRequests::whereIn('refId', $data['leaveRequests'])
                ->where(function ($query) use ($data) {
                    $query->whereNull('canBeReviewedBy')
                        ->orWhere('canBeReviewedBy', '!=', $data['userId']);
                })
                ->get();

            if ($reassign->isEmpty()) {
                throw new Exception('No leave requests can be reassigned.', JsonResponse::HTTP_CONFLICT);
            }

            $ids = [];
            foreach ($reassign as $request) {
                if ($request->user->employee->addedBy === $data['userId'] || $request->user_id === $data['userId']) {
                    throw new Exception('Invalid request. Leave request cannot be reassigned.', JsonResponse::HTTP_CONFLICT);
                }
                $ids[] = $request->id;
            }

            $affectedRows = TimeOffRequests::whereIn('id', $ids)->update(['canBeReviewedBy' => $data['userId']]);

            DB::commit();

            return $affectedRows;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
            throw new Exception('An error occurred while processing your request.', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Retrieve a users upcoming timeOffs, employees currently on timeoff and employees returning from time off.
     *
     * @return mixed
     */
    public function timeOffInfo()
    {
        $allTimeOffRequests = TimeOffRequests::where('status', 'approved')
            ->where(function ($query) {
                $query->where('startDate', '>=', now())
                    ->where('startDate', '<', now()->addWeek()->addDay())
                    ->orWhere(function ($query) {
                        $query->where('startDate', '<=', now())
                            ->where('endDate', '>=', now());
                    })
                    ->orWhere(function ($query) {
                        $query->where('endDate', '<', now())
                            ->where('endDate', '>=', Carbon::now()->subDays(5));
                    });
            })->get();

        $filteredHolidays = $this->fetchFilteredHolidays();

        $upcomingTimeOffs = $allTimeOffRequests->filter(function ($request) {
            return $request->startDate >= now() && $request->startDate < now()->addWeek()->addDay();
        });

        $employeesOnTimeOff = $allTimeOffRequests->filter(function ($request) {
            return $request->startDate <= now() && $request->endDate >= now();
        });

        $cutOffDate = Carbon::now()->subDays(5);
        $employeesReturningFromTimeOff = $allTimeOffRequests->filter(function ($request) use ($cutOffDate) {
            return $request->endDate < now() && $request->endDate >= $cutOffDate;
        });
        $employeesOnTimeOffOverView = $this->calculateTotalCountsOfTimeOff($employeesOnTimeOff);
        return [
            'upcomingTimeOffs' => $upcomingTimeOffs,
            'employeesOnTimeOff' => $employeesOnTimeOff,
            'employeesOnTimeOffOverView' => $employeesOnTimeOffOverView,
            'employeesReturningFromTimeOff' => $employeesReturningFromTimeOff,
            'upcomingHolidays' => $filteredHolidays,
        ];
    }
    /**
     * calculateTotalCountsOfTimeOff overview
     * @param $employeesOnTimeOff
    */
    private function calculateTotalCountsOfTimeOff($employeesOnTimeOff){
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $percentageCurrentMonth = 0;
        $percentagePreviousMonth = 0;
        $type = "";

        $currentMonthsCounts = $employeesOnTimeOff->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
        $previousMonthsCounts = $employeesOnTimeOff->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $totalTimeOffCount = $employeesOnTimeOff->count();
        
        if ($totalTimeOffCount > 0) {
          $percentageCurrentMonth = ($currentMonthsCounts / $totalTimeOffCount) * 100;
          $percentagePreviousMonth = ($previousMonthsCounts / $totalTimeOffCount) * 100;
        }
        if($currentMonthsCounts > $previousMonthsCounts){
            $type = "Increased";
        }
        else if($currentMonthsCounts == $previousMonthsCounts){
           $type = "Stable";

        }else{
            $type = "Decreased";
        }
        
        return [
            'type'=>$type,
            'percentage' =>round(abs($percentageCurrentMonth - $percentagePreviousMonth),1),

        ];
    }

    /**
     * Fetch all holidays
     */
    protected function fetchFilteredHolidays()
    {
        $holidays = Holiday::where('date', '>', now())->get(['holiday', 'date', 'timeZone']);

        $filteredHolidays = [];

        foreach ($holidays as $holiday) {
            $excludedWords = ['Equinox', 'Solstice', 'start', 'holy' , 'Sunday','Day After','Remembrance',
            'Valentine','Epiphany','Franco','Shrove','Carnival','Hizir','Patrick','Anniversary','Maundy','Alevitic'];

            $skipHoliday = false;
            foreach ($excludedWords as $word) {
                if (stripos($holiday->holiday, $word) !== false) {
                    $skipHoliday = true;
                    break;
                }
            }

            if (!$skipHoliday) {
                $holidayDate = Carbon::parse($holiday->date);

                if ($holidayDate->isFuture()) {

                    $country = $this->getCountryFromTimezone($holiday->timeZone);

                    $filteredHolidays[] = [
                        'title' => 'holiday',
                        'name' => $holiday->holiday,
                        'date' => $holidayDate->format('jS F Y'),
                        'country' => $country,
                    ];
                }
            }
        }

        return $filteredHolidays;
    }

    /**
     * Get the country using the timezone
     */
    protected function getCountryFromTimezone($timezone)
    {
        $timezoneToCountry = [
            'Africa/Accra' => 'Ghana',
            'Europe/Berlin' => 'Germany',
            'Africa/Kigali' => 'Rwanda',
        ];

        return $timezoneToCountry[$timezone] ?? 'Unknown';
    }


    /**
     * Get time-off requests based on user role and filter criteria.
     *
     * @return array
     * @throws NotFoundResourceException
     */
    public function timeOffRequest()
    {
        $authId = auth()->user();
        $isAdmin = auth()->user()->employee->roleId === Roles::getRoleIdByValue(Roles::ADMIN->value);

        $query = TimeOffRequests::query();

        if (!$isAdmin) {
            $query->where(function ($query) use ($authId) {
                $query->whereNot('userId',$authId->id)->where('canBeReviewedBy', $authId->id)
                    ->orWhereHas('user.employee', function ($query) use ($authId) {
                        $query->where('addedBy', $authId->employee->id);
                    });
            });
        }

        $allTimeOffRequests = $query->get();
        $pendingTimeOffRequests = $allTimeOffRequests->where('status', 'pending');
        $rejectedTimeOffRequests = $allTimeOffRequests->where('status', 'rejected');
        $approvedTimeOffRequests = $allTimeOffRequests->where('status', 'approved');

        return [
            'allTimeOffRequests' => $pendingTimeOffRequests,
            'rejectedTimeOffRequests' => $rejectedTimeOffRequests,
            'approvedTimeOffRequests' => $approvedTimeOffRequests
        ];
    }



    /**
     * Book leave for an employee.
     *
     * @param array $data
     * @return mixed
     */
    public function bookLeave(array $data)
    {
        try {
            DB::beginTransaction();
            $leaveRequest = TimeOffRequests::firstOrCreate(['userId' => $data['userId'], 'type' => $data['type'], 'status' => 'pending', 'startDate' => $data['startDate'], 'endDate' => $data['endDate']], $data);
            DB::commit();
            return $leaveRequest;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
        }
    }

    /**
     * Check if the user has used up their allocated annual leave.
     *
     * @param int $leaveType The ID of the leave type to check.
     * @param int $userId The ID of the user for whom to check the leave allocation.
     * @return bool True if the user has used up their allocated annual leave, false otherwise.
     */
    public function hasUsedUpAnnualLeave($leaveType, $userId)
    {
        $leave = TimeOffType::where('id', $leaveType)->first();

        if ($leave && strtolower($leave->name) === 'annual leave') {
            $currentYear = Carbon::now()->year;
            $startOfYear = Carbon::create($currentYear, 1, 1);
            $endOfYear = Carbon::create($currentYear, 12, 31);

            $timeOffRequests = TimeOffRequests::where('userId', $userId)
                ->where('type', $leave->id)
                ->where('status', 'approved')
                ->where(function ($query) use ($startOfYear, $endOfYear) {
                    $query->whereBetween('startDate', [$startOfYear, $endOfYear])
                        ->orWhereBetween('endDate', [$startOfYear, $endOfYear])
                        ->orWhere(function ($query) use ($startOfYear, $endOfYear) {
                            $query->where('startDate', '<', $startOfYear)
                                ->where('endDate', '>', $endOfYear);
                        });
                })
                ->get();

            $totalLeaveDays = 0;

            foreach ($timeOffRequests as $request) {
                $startDate = Carbon::parse($request->startDate)->max($startOfYear);
                $endDate = Carbon::parse($request->endDate)->min($endOfYear);

                $totalLeaveDays += $startDate->diffInDays($endDate) + 1;
            }

            if ($totalLeaveDays >= 30) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }



    /**
     * Retrieve all leave types.
     *
     * @return mixed
     */
    public function leaveTypes()
    {
        $leaveTypes = TimeOffType::all();
        if ($leaveTypes == null) {
            throw new NotFoundResourceException('No leave types found');
        }
        return $leaveTypes;
    }

    /**
     * Retrieve information about employees on leave.
     *
     * @return mixed
     */
    public function employeesOnLeave()
    {
        try {
            $peopleOnLeave = TimeOffRequests::where("status", "approved")->get();
            return $peopleOnLeave;
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    /**
     * Retrieve pending leave requests.
     *
     * @return mixed
     */
    public function pendingLeaves()
    {

        $peopleOnLeave = TimeOffRequests::where("status", "pending")->get();
        if ($peopleOnLeave == null) {
            throw new NotFoundResourceException('No pending leave requests found.');
        }
        return $peopleOnLeave;
    }
    /**
     * Get leave history of a user.
     *
     * @return mixed
     */
    public function leaveHistory()
    {
        $leaveHistory = TimeOffRequests::where('userId', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($leaveHistory->isEmpty()) {
            throw new NotFoundResourceException('No leave history found');
        }

        return $leaveHistory;
    }



    /**
     * Manage the approval or rejection of a leave request.
     *
     * @param string $action The action to perform (approve or reject).
     * @param string $refId The reference ID of the leave request.
     * @return \App\Models\TimeOffRequests|null The updated leave request if successful; otherwise, null.
     */
    public function manageLeave(string $action, string $refId)
    {
        try {
            DB::beginTransaction();
            $user = auth()->user()->employee;

            $request = TimeOffRequests::where('refId', $refId)
                ->where("status", "pending")
                ->first();

            if (!$request) {
                throw new NotFoundResourceException('Leave request not found');
            }

            if ($user->roleId === Roles::getRoleIdByValue(Roles::MGT->value) && $user->id === $request->userId) {
                throw new AuthenticationException('You are not authorized to manage your own leave request');
            }

            $request->status = $action;
            $request->reviewedBy = $user->id;
            $request->save();

            DB::commit();
            return $request;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
        }
    }


    /**
     * Create a new leave type.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        try {
            DB::beginTransaction();
            $leaveType = TimeOffType::firstOrCreate(['name' => $data['name']], $data);
            DB::commit();
            return $leaveType;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
        }
    }

    /**
     * Update details of a leave type.
     *
     * @param array $data
     * @param string $refId
     * @return mixed
     */
    public function update(array $data, string $refId)
    {
        try {
            DB::beginTransaction();
            $leaveType = TimeOffType::where('refId', $refId)->first();
            $leaveType->update($data);
            DB::commit();
            return $leaveType;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
        }
    }

    /**
     * Find a leave type by its reference ID.
     *
     * @param string $refId
     * @return mixed
     */
    public function findLeaveTypeByRefId(string $refId)
    {
        try {
            DB::beginTransaction();
            $leaveType = TimeOffType::where('refId', $refId)->first();
            DB::commit();
            return $leaveType;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
        }
    }

    /**
     * Delete a leave type by its ID.
     *
     * @param string $refId
     * @return mixed
     */
    public function deleteLeaveType(string $refId)
    {
        try {
            DB::beginTransaction();
            $leaveType = TimeOffType::where('refId', $refId);
            $leaveType->delete();
            DB::commit();
            return $leaveType;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
            Log::error($e);
        }
    }
     /**
     * Fetch all past holidays
     */
    public function passedHolidays()
    {
        $holidays = Holiday::where('date', '<', now())->get(['holiday', 'date', 'timeZone']);

        $filteredHolidays = [];

        foreach ($holidays as $holiday) {
            $excludedWords = ['Equinox', 'Solstice', 'start', 'holy' ,'Sunday','Day After','Remembrance',
            'Valentine','Epiphany','Franco','Shrove','Carnival','Hizir','Patrick','Anniversary','Maundy','Alevitic'];

            $skipHoliday = false;
            foreach ($excludedWords as $word) {
                if (stripos($holiday->holiday, $word) !== false) {
                    $skipHoliday = true;
                    break;
                }
            }

            if (!$skipHoliday) {
                $holidayDate = Carbon::parse($holiday->date);

                if ($holidayDate->isPast()) {

                    $country = $this->getCountryFromTimezone($holiday->timeZone);

                    $filteredHolidays[] = [
                        'title' => 'holiday',
                        'name' => $holiday->holiday,
                        'date' => $holidayDate->format('jS F Y'),
                        'country' => $country,
                        'timeZone' => $holiday->timeZone,
                    ];
                }
            }
        }

        return $filteredHolidays;
    }
}