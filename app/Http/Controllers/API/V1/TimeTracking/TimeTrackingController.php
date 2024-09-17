<?php

namespace App\Http\Controllers\API\V1\TimeTracking;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeTracking\TrackTimeRequest;
use App\Http\Requests\TimeTracking\UpdateTrackedTimeRequest;
use App\Service\V1\TimeTracking\TimeTrackingServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for handling time tracking related operations.
 */
class TimeTrackingController extends Controller
{
    /**
     * Create a new TimeTrackingController instance.
     *
     * @param TimeTrackingServiceInterface $timeTrackingService The time tracking service instance.
     */
    public function __construct(public TimeTrackingServiceInterface $timeTrackingService)
    {
    }

    /**
     * Delete a time tracking record by its ID.
     *
     * @param int $id The ID of the time tracking record to delete.
     * @return JsonResponse The JSON response containing the result of the operation.
     */
    public function deleteTime(int $id)
    {
        try {
            $response = $this->timeTrackingService->deleteTimeTrack($id);
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all time tracking records.
     *
     * @return JsonResponse The JSON response containing the time tracking records.
     */
    public function getTimeTracks()
    {
        try {
            $response = $this->timeTrackingService->getTimeTracks();
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Track time by creating a new time tracking record.
     *
     * @param TrackTimeRequest $request The request containing the data for the new time tracking record.
     * @return JsonResponse The JSON response containing the result of the operation.
     */
    public function trackTime(TrackTimeRequest $request)
    {
        try {
            $response = $this->timeTrackingService->trackTime($request->all());
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a time tracking record with the specified ID.
     *
     * @param int $id The ID of the time tracking record to update.
     * @param UpdateTrackedTimeRequest $request The request containing the updated data for the time tracking record.
     * @return JsonResponse The JSON response containing the result of the operation.
     */
    public function updatetrackedTime(int $id, UpdateTrackedTimeRequest $request)
    {
        try {
            $response = $this->timeTrackingService->updateTrackTime($id, $request->all());
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
