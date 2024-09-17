<?php

namespace App\Service\V1\TimeTracking;

/**
 * Interface for the TimeTrackingService.
 */
interface TimeTrackingServiceInterface
{
    /**
     * Delete a time tracking record by its ID.
     *
     * @param int $id The ID of the time tracking record to delete.
     * @return mixed The result of the deletion operation.
     */
    public function deleteTimeTrack(int $id);

    /**
     * Get all time tracking records.
     *
     * @return mixed The collection of time tracking records.
     */
    public function getTimeTracks();

    /**
     * Track time by creating a new time tracking record.
     *
     * @param array $data The data for the new time tracking record.
     * @return mixed The result of the tracking operation.
     */
    public function trackTime(array $data);

    /**
     * Update a time tracking record with the specified ID and data.
     *
     * @param int $id The ID of the time tracking record to update.
     * @param array $data The data to update the time tracking record with.
     * @return mixed The result of the update operation.
     */
    public function updateTrackTime(int $id, array $data);
}
