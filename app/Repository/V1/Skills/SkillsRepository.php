<?php

namespace App\Repository\V1\Skills;

use Exception;
use App\Models\V1\skill\Skill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Skills\UserSkills;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class SkillsRepository implements SkillsInterfaceRepository
{
    /**
     * Fetch all skills.
     *
     * @return mixed
     */
    public function fetch()
    {
        try {
          
            $Skills = Skill::select('name')
                ->selectRaw('LOWER(name) as lower_name')->distinct('lower_name')->orderBy('lower_name', 'asc')->get();

            return $Skills;
        } catch (Exception $e) {
            return null;
        }
    }



    /**
     * Fetch all skills by auth.
     *@param string $employee_id
     * @return mixed
     */
    public function fetchByAuth(string $employee_id)
    {
        try {
            $Skills = Skill::where('employee_id', '=', $employee_id)->orderBy('name', 'asc')->get();
            return $Skills;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     *@return array $Skillss
     * @param array < int , string
     */
    // Save a new Skills with an array of data
    public function save(array $SkillsData)
    {
        try {
            DB::beginTransaction();
            // Create a new Skills instance and save it
            $Skills = Skill::create($SkillsData);
            DB::commit();
            return $Skills;
        } catch (\Exception $e) {
            DB::rollBack();
            return null;
        }
    }

    /**
     * Update skills by skill ID.
     *
     * @param array $skillsData
     * @return mixed
     */
    public function updateById(array $SkillsData)
    {
        try {
            DB::beginTransaction();
            // Find Skills instance and update the hashed password
            $Skills = Skill::where('id', '=', $SkillsData['id'])->update($SkillsData);
            DB::commit();
            return $Skills;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Delete skills by skill ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        try {
            DB::beginTransaction();
            Skill::where("id", "=", $id)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    /**
     * Store skills in the database.
     *
     * @param array $data
     * @return array|false
     */
    public function store(array $data)
    {
        try {
            DB::beginTransaction();

            $skills = [];
            foreach ($data as $skillData) {
                $skills[] = Skill::updateOrCreate(
                    ['name' => $skillData['name'], 'employee_id' => $skillData['employee_id']],
                    $skillData
                );
            }

            DB::commit();
            return $skills;
          
        }catch (Exception $e) {
            DB::rollBack();
            throw new Exception('Failed to store skills. Please try again later.',);
        }
    }

    /**
     * Update skills for an employee by name.
     *
     * @param string $name
     * @param int $employee_id
     * @return bool
     */
    public function updateByName(string $name, $employee_id): bool
    {
        try {
            DB::beginTransaction();
            $skills = Skill::where("name", "=", $name)->first();
            Skill::where('employee_id', "=", $employee_id)->update(['skills_id' => $skills['id']]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Update user skills for an employee by name.
     *
     * @param string $name
     * @param mixed $employee_id
     * @return bool
     */
    public function updateUserSkillsByName(string $name, $employee_id): bool
    {
        try {
            DB::beginTransaction();
            Skill::where('employee_id', "=", $employee_id)->update(["name" => $name]);
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}