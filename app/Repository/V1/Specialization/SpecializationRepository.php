<?php

namespace App\Repository\V1\Specialization;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Specialization\Specialization;
use App\Models\V1\Specialization\UserSpecialization;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SpecializationRepository implements SpecializationInterfaceRepository
{
     /**
     * Fetch a single Specializations
     *
     * @return mixed
     */
    public function getASpecilization(int $specialization){
        try {
            $Specialization = Specialization::find($specialization);
            return $Specialization;
        } catch (Exception $e) {
            return null;
        }
    }
    /**
     * Fetch all specializations.
     *
     * @return mixed
     */
    public function fetch()
    {
        try {
            $Specialization = Specialization::orderBy('name','asc')->get();
            return $Specialization;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Save new specialization.
     *
     * @param array $specializationData
     * @return mixed
     */
    public function save(array $SpecializationData)
    {
        try {
            DB::beginTransaction();
            // Create a new Specialization instance and save it
            $Specialization = Specialization::create($SpecializationData);
            DB::commit();
            return $Specialization;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    /**
     * Update specialization by specialization ID.
     *
     * @param array $specializationData
     * @return mixed
     */
    public function updateById(array $SpecializationData)
    {
        try {
            DB::beginTransaction();
            // Find Specialization instance and update the hashed password
            $Specialization = Specialization::where('id', '=', $SpecializationData['id'])->update($SpecializationData);
            DB::commit();
            return $Specialization;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Delete specialization by specialization ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        try {
            DB::beginTransaction();
            Specialization::where("id", "=", $id)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Store specialization for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function storeByName(string $name, $employee_id): bool
    {
        try {
            DB::beginTransaction();
            $specialization =  Specialization::whereRaw('LOWER(name) = ?', [$name])->first();
            UserSpecialization::create(['employee_id' => $employee_id, 'specialization_id' => $specialization['id']]);
            DB::commit();
            return true;
        } catch (Exception $e) {

            Log::info($e);
            DB::rollBack();
            return false;
        }
    }

    /**
     * Update specialization for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function updateByName(string $name, $employee_id): bool
    {
        try {
            DB::beginTransaction();
            $specialization =  Specialization::whereRaw('LOWER(name) = ?', [$name])->first();
            UserSpecialization::where('employee_id', "=", $employee_id)->update(['specialization_id' => $specialization['id']]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}