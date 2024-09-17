<?php

namespace App\Service\V1\Department;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Department\FetchDepartment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Department\DepartmentInterfaceRepository;

class DepartmentService implements DepartmentInterfaceService
{
    /**
     * @var DepartmentInterfaceRepository
     */
    public $departmentRepository;

    /**
     * DepartmentService constructor.
     * @param DepartmentInterfaceRepository $departmentRepository
     */
    public function __construct(
        DepartmentInterfaceRepository $departmentRepository,
    ) {
        $this->departmentRepository = $departmentRepository;
    }

    /**
     * Fetch all departments.
     *
     * @return array
     */
    public function fetch(): array
    {
        try {
            $departments = $this->departmentRepository->fetch();

            return [
                'departments' => FetchDepartment::collection(collect($departments)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (\Exception $e) {
            // Handle exceptions
            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Store a new department.
     *
     * @param $request
     * @return array
     */
    public function store($request): array
    {
        try {
            $cleanData = $request->validated();

            $departmentData = [
                'name' => strtoupper($cleanData['name']),
            ];

            $department = $this->departmentRepository->save($departmentData);

            if (empty($department)) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Department created successfully.',
                'department' => new FetchDepartment($department),
                'status' => JsonResponse::HTTP_CREATED,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Department creation was not successful, please try again.',
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
     * Update an existing department.
     *
     * @param $request
     * @return array
     */
    public function update($request): array
    {
        try {
            $cleanData = $request->validated();

            $departmentData = [
                'id' => $cleanData['department_id'],
                'name' => $cleanData['name'],
            ];

            $department = $this->departmentRepository->updateById($departmentData);

            if (!$department) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Department updated successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Department update was not successful, please try again.',
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
     * Delete a department.
     *
     * @param $request
     * @return array
     */
    public function delete($request): array
    {
        try {
            $cleanData = $request->validated();

            $department = $this->departmentRepository->deleteById($cleanData['department_id']);

            if (!$department) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'Department deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Department deletion was not successful, please try again.',
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
