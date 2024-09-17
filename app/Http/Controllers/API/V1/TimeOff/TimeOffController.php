<?php

namespace App\Http\Controllers\API\V1\TimeOff;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeOff\BookTimeOffRequest;
use App\Http\Requests\TimeOff\CreateTimeOffTypeRequest;
use App\Http\Requests\TImeOff\ReassignLeaveRequest;
use App\Http\Requests\TimeOff\UpdateTimeOffTypeRequest;
use App\Service\V1\TimeOff\TimeOffServiceInterface;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TimeOffController extends Controller
{
    public function __construct(public TimeOffServiceInterface $timeOffService)
    {
    }

    /**
     * Reassign leave requests based on the provided data.
     *
     * @param ReassignLeaveRequest $request The validated request containing reassignment data.
     * @return JsonResponse JSON response indicating the status of the reassignment.
     */
    public function reassignLeaveRequest(ReassignLeaveRequest $request)
    {
        try {
            $response = $this->timeOffService->reassignLeaveRequest($request->validated());
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of people on leave.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeesOnLeave()
    {
        try {
            $response = $this->timeOffService->employeesOnLeave();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of leave types.
     *
     * @return \Illuminate\Http\Response
     */
    public function leaveTypes()
    {
        try {
            $response = $this->timeOffService->leaveTypes();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of people who are waiting for their leaves to be approved.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingLeaves()
    {
        try {
            $response = $this->timeOffService->pendingLeaves();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a listing of people who are waiting for their leaves to be approved.
     *
     * @return \Illuminate\Http\Response
     */
    public function leaveHistory()
    {
        try {
            $response = $this->timeOffService->leaveHistory();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Approve or Reject a pending leave request.
     *
     * @param string $action Action to perform (approve-leave or reject-leave).
     * @param string $refId Reference ID of the leave request.
     * @return \Illuminate\Http\Response
     */
    public function manageLeave(string $action, string $refId)
    {
        try {
            $response = $this->timeOffService->manageLeave($action, $refId);

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateTimeOffTypeRequest $request)
    {
        try {
            $response = $this->timeOffService->create($request->validated());

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTimeOffTypeRequest $request, string $refId)
    {
        try {
            $response = $this->timeOffService->update($request->validated(), $refId);

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteLeaveType(string $refId)
    {
        try {
            $response = $this->timeOffService->deleteLeaveType($refId);

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Book a leave.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bookLeave(BookTimeOffRequest $request)
    {
        try {
            $response = $this->timeOffService->bookLeave($request->all());

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
