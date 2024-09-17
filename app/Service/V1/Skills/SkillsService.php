<?php

namespace App\Service\V1\Skills;

use Exception;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\Log;
use App\Http\Resources\Skills\FetchSkills;
use App\Repository\V1\Skills\SkillsInterfaceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SkillsService implements SkillsInterfaceService
{
    public $skillsRepository, $employeeRepository;
    /**
     * SkillsController constructor.
     *
     * Initializes a new instance of the SkillsController.
     *
     * @param SkillsInterfaceRepository $skillsRepository
     *     The repository for managing skills.
     * @param EmployeeRepository $employeeRepository
     *     The repository for managing employee information.
     */
    public function __construct(
        SkillsInterfaceRepository $skillsRepository,
    ) {
        $this->skillsRepository = $skillsRepository;
    }

    /**
     * fetch all  skills
     * @param $request
     * @return mixed skills
     */
    public function fetch()
    {
        try {
            //pass the data for query
            $skills = $this->skillsRepository->fetch();
            return [
                'skills' => FetchSkills::collection(collect($skills)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }


    /**
     * fetch all  skills by authenticated
     * @param $request
     * @return mixed skills
     */

    public function fetchByAuth($request)
    {
        try {
            $employee_id = $request->user()->employee->id;
            //pass the data for query
            $skills = $this->skillsRepository->fetchByAuth($employee_id);
            return [
                'skills' => FetchSkills::collection(collect($skills)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Other exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Store skills based on the request data.
     *
     * @param Illuminate\Http\Request $request
     * @return array
     */
    public function store($request)
    {
        try {
            $employeeId = auth()->user()->employee->id;

            $validatedSkills = $request->validated()['skills'];

            $skillsData = [];
            foreach ($validatedSkills as $skill) {
                $skillsData[] = [
                    'specializationId' => $skill['specializationId'],
                    'name' => $skill['name'],
                    'rating' => $skill['rating'],
                    'employee_id' => $employeeId,
                ];
            }

            $skills = $this->skillsRepository->store($skillsData);

            $message = $this->getMessageFromSkills($skills);

            return [
                'message' => $message,
                'status' => JsonResponse::HTTP_CREATED,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Skills creation was not successful because user was not found, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Determine the appropriate message based on the created and updated skills count.
     *
     * @param array $skills
     * @return string
     */
    private function getMessageFromSkills($skills)
    {
        $createdSkillsCount = 0;
        $updatedSkillsCount = 0;

        foreach ($skills as $skill) {
            if ($skill->wasRecentlyCreated) {
                $createdSkillsCount++;
            } else {
                $updatedSkillsCount++;
            }
        }

        switch (true) {
            case ($createdSkillsCount == 1 && $updatedSkillsCount == 0):
                return 'Skill was created successfully.';
            case ($createdSkillsCount > 0 && $updatedSkillsCount == 0):
                return 'All skills were created successfully.';
            case ($createdSkillsCount == 0 && $updatedSkillsCount == 1):
                return 'Skill was updated successfully.';
            case ($createdSkillsCount == 0 && $updatedSkillsCount > 0):
                return 'All skills were updated successfully.';
            case ($createdSkillsCount > 0 && $updatedSkillsCount > 0):
                return 'Some skills were created and some were updated successfully.';
            default:
                throw new Exception('No skills were created or updated.');
        }
    }

    /**
     * update a skill object
     * @param $request
     * @return mixed
     */
    public function update($request)
    {
        try {
            $cleanData = $request->validated();
            $skillsData = [
                'id' => $cleanData['skills_id'],
                'name' => $cleanData['name'],
            ];

            $skills = $this->skillsRepository->updateById($skillsData);
            if (!$skills) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Skills updated successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Skill update was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * delete a skill
     * @param $request
     * @return mixed
     */
    public function delete($request)
    {
        try {
            $cleanData = $request->validated();
            $skills = $this->skillsRepository->deleteById($cleanData['skills_id']);

            if (!$skills) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Skill deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Department deleting was not successful, please try again.',
                'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
}
