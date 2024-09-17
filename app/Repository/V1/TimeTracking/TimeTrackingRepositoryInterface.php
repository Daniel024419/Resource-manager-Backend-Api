<?php

namespace App\Repository\V1\TimeTracking;

/**
 * Interface for the TimeTrackingRepository.
 */
interface TimeTrackingRepositoryInterface
{
    /**
     * Delete a time tracking record by its ID.
     *
     * @param int $id The ID of the time tracking record to delete.
     * @return bool True if the deletion was successful, otherwise false.
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
     * @return mixed The created time tracking record.
     */
    public function trackTime(array $data);

    /**
     * Update a time tracking record with the specified ID and data.
     *
     * @param int $id The ID of the time tracking record to update.
     * @param array $data The data to update the time tracking record with.
     * @return bool True if the update was successful, otherwise false.
     */
    public function updateTrackTime(int $id, array $data);
}
