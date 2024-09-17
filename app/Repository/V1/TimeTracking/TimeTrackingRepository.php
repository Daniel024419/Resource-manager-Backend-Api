<?php

namespace App\Repository\V1\TimeTracking;

use App\Models\V1\TimeTracking\TimeTracking;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeTrackingRepository implements TimeTrackingRepositoryInterface
{
    /**
     * Delete a time tracking record by its ID.
     *
     * @param int $id The ID of the time tracking record to delete.
     * @return bool True if the record was successfully deleted, false otherwise.
     * @throws \Exception If an error occurs while deleting the record.
     */
    public function deleteTimeTrack($id)
    {
        try {
            DB::beginTransaction();
            $timeTrack = TimeTracking::findOrFail($id);

            $deleted = $timeTrack->forceDelete();

            DB::commit();
            return $deleted;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Retrieve all time tracking records for the current user's employee.
     *
     * @return \Illuminate\Database\Eloquent\Collection|\App\Models\V1\TimeTracking\TimeTracking[]
     */
    public function getTimeTracks()
    {
        $timeTracks = TimeTracking::where('employeeId', auth()->user()->employee->id)->with('employeeProject')->get();

        $filteredProjects = $timeTracks->filter(function ($timeTrack) {
            return $timeTrack->employeeProject != null;
        });
        
        return $filteredProjects;
    }

    /**
     * Update a time tracking record with the specified ID and data.
     *
     * @param int $id The ID of the time tracking record to update.
     * @param array $data The data to update the record with.
     * @return bool True if the record was successfully updated, false otherwise.
     * @throws \Exception If an error occurs while updating the record.
     */
    public function updateTrackTime(int $id, array $data)
    {
        try {
            DB::beginTransaction();

            $timeTrack = TimeTracking::findOrFail($id);

            $data['employeeProjectId'] = $data['id'];

            $this->validateTimeLimits($data, $timeTrack->employeeId, $timeTrack->employeeProjectId, $data['date'], $id);

            $data = $this->filterDataKeys($data, ['task', 'date', 'startTime', 'endTime', 'employeeProjectId']);

            $updated = $timeTrack->update($data);

            DB::commit();

            return $updated;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Track time for a new time tracking record.
     *
     * @param array $data The data for the new time tracking record.
     * @return \App\Models\V1\TimeTracking\TimeTracking The newly created time tracking record.
     * @throws \Exception If an error occurs while tracking time.
     */
    public function trackTime($data)
    {
        try {
            DB::beginTransaction();
            $employeeId = auth()->user()->employee->id;
            $employeeProjectId = $data['id'];

            $this->validateTimeLimits($data, $employeeId, $employeeProjectId, $data['date']);

            $data['employeeId'] = $employeeId;
            $data['employeeProjectId'] = $employeeProjectId;

            $data = $this->filterDataKeys($data, ['task', 'date', 'startTime', 'endTime', 'employeeId', 'employeeProjectId']);

            $trackTime = TimeTracking::firstOrCreate($data);

            DB::commit();
            return $trackTime;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate time limits based on work hours and existing time tracks.
     *
     * @param array $data The data containing work hours, start time, end time, etc.
     * @param int $employeeId The ID of the employee.
     * @param int $employeeProjectId The ID of the employee's project.
     * @param string $date The date of the time track.
     * @param int|null $id The ID of the time track to ignore, if any.
     * @return void
     * @throws Exception
     */
    protected function validateTimeLimits(array $data, int $employeeId, int $employeeProjectId, string $date, ?int $id = null): void
    {
        $limit = $data['workHours'] + 4;
        $hoursSpent = Carbon::parse($data['endTime'])->diffInRealHours(Carbon::parse($data['startTime']));
        $totalHoursSpent = $this->totalHourseSpent($employeeId, $employeeProjectId, $date, [$id]);

        if ($totalHoursSpent > $limit || ($hoursSpent + $totalHoursSpent) > $limit) {
            if (($hoursSpent + $totalHoursSpent) > $limit) {
                $dateToShow = Carbon::parse($date)->isToday() ? 'today' : $date;
                throw new Exception('The total work hours limit for ' . $dateToShow . ' has been exceeded.');
            } else {
                throw new Exception('The time range overlaps with existing clocked time. Please choose another time slot.');
            }
        }
    }

    /**
     * Filter data array to keep only specified keys.
     *
     * @param array $data The data array to filter.
     * @param array $keysToKeep The keys to keep in the data array.
     * @return array The filtered data array.
     */
    protected function filterDataKeys(array $data, array $keysToKeep): array
    {
        return array_intersect_key($data, array_flip($keysToKeep));
    }

    /**
     * Calculate the total hours spent on a project for a specific date, excluding certain records.
     *
     * @param int $employeeId The ID of the employee.
     * @param int $employeeProjectId The ID of the employee project.
     * @param string $date The date for which to calculate the total hours spent.
     * @param array|null $excludeIds Optional array of record IDs to exclude from the calculation.
     * @return float The total hours spent on the project for the specified date.
     */
    protected function totalHourseSpent($employeeId, $employeeProjectId, $date, $excludeIds = null)
    {
        $query = TimeTracking::where('employeeId', $employeeId)
            ->where('employeeProjectId', $employeeProjectId)
            ->where('date', $date)
            ->get();

        if ($excludeIds !== null) {
            $query = $query->filter(function ($record) use ($excludeIds) {
                return !in_array($record->id, $excludeIds);
            });
        }

        return $query->sum(function ($record) {
            return $record->endTime->diffInRealHours($record->startTime);
        });
    }
}
