<?php

namespace App\Service\V1\TimeTracking;

use App\Http\Resources\TimeTrack\GetTimeTracksResource;
use App\Repository\V1\TimeTracking\TimeTrackingRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Termwind\Components\Hr;

class TimeTrackingService implements TimeTrackingServiceInterface
{
    /**
     * Create a new TimeTrackingService instance.
     *
     * @param TimeTrackingRepositoryInterface $timeTrackingRepository The time tracking repository.
     */
    public function __construct(public TimeTrackingRepositoryInterface $timeTrackingRepository)
    {
    }

    /**
     * Delete a time tracking record by its ID.
     *
     * @param int $id The ID of the time tracking record to delete.
     * @return array The response message and status code.
     * @throws Exception If an error occurs while deleting the record.
     */
    public function deleteTimeTrack($id)
    {
        try {
            $trackTime = $this->timeTrackingRepository->deleteTimeTrack($id);
            if (!$trackTime) {
                throw new Exception('Failed to delete the time tracking record.');
            }

            return [
                'message' => 'The time tracking record has been deleted successfully.',
                'status' => JsonResponse::HTTP_OK
            ];
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve all time tracking records for the current user's employee.
     *
     * @return array The response message, track times, and status code.
     * @throws Exception If no time tracks are found.
     */
    public function getTimeTracks()
    {
        try {
            $trackTimes = $this->timeTrackingRepository->getTimeTracks();
           
            return [
                'message' => 'Time tracking records fetched successfully.',
                'trackTimes' => GetTimeTracksResource::collection($trackTimes),
                'status' => JsonResponse::HTTP_OK
            ];
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a time tracking record with the specified ID and data.
     *
     * @param int $id The ID of the time tracking record to update.
     * @param array $data The data to update the record with.
     * @return array The response message and status code.
     * @throws Exception If an error occurs while updating the record.
     */
    public function updateTrackTime($id, $data)
    {
        try {
            $trackTime = $this->timeTrackingRepository->updateTrackTime($id, $data);
            if (!$trackTime) {
                throw new Exception('Failed to update the time tracking record.');
            }
            return [
                'message' => 'The time tracking record has been updated successfully.',
                'status' => JsonResponse::HTTP_OK
            ];
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw $e;
        }
    }

    /**
     * Track time for a new time tracking record.
     *
     * @param array $data The data for the new time tracking record.
     * @return array The response message and status code.
     * @throws Exception If an error occurs while tracking time.
     */
    public function trackTime($data)
    {
        try {
            $trackTime = $this->timeTrackingRepository->trackTime($data);
            if (!$trackTime) {
                throw new Exception('Failed to track time.');
            }
            return [
                'message' => 'Your time has been tracked successfully.',
                'status' => JsonResponse::HTTP_OK
            ];
        } catch (Exception $e) {
            Log::info($e->getMessage());
            throw $e;
        }
    }
}
