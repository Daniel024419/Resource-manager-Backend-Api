<?php

namespace App\Http\Controllers\API\V1\Department;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Service\V1\Department\DepartmentInterfaceService;
use App\Http\Requests\Department\DepartmentfetchRequest;
use App\Http\Requests\Department\DepartmentStoreRequest;
use App\Http\Requests\Department\DepartmentDeleteRequest;
use App\Http\Requests\Department\DepartmentUpdateRequest;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;

class DepartmentController extends Controller
{

    protected $departmentService, $deleteResponseHandler, $updateResponseHandler, $storeResponseHandler, $fetchResponseHandler;
    /**
     * Constructor for the DepartmentController.
     *
     * @param DepartmentInterfaceService $departmentService
     * @param StoreResponse $storeResponseHandler
     * @param DeleteResponse $deleteResponseHandler
     * @param UpdateResponse $updateResponseHandler
     * @param FetchResponse $fetchResponseHandler
     */
    public function __construct(
        DepartmentInterfaceService $departmentService,
        StoreResponse $storeResponseHandler,
        DeleteResponse $deleteResponseHandler,
        UpdateResponse $updateResponseHandler,
        FetchResponse $fetchResponseHandler
    ) {
        $this->departmentService = $departmentService;
        $this->storeResponseHandler = $storeResponseHandler;
        $this->deleteResponseHandler = $deleteResponseHandler;
        $this->updateResponseHandler = $updateResponseHandler;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }

    /**
     * fetch for Departments
     *
     * @param DepartmentfetchRequest $request
     * @return JsonResponse
     */
    public function fetch(): JsonResponse
    {
        try {
            $fetchResponse = $this->departmentService->fetch();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created department in storage.
     *
     * @param  \App\Http\Requests\Department\DepartmentStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(DepartmentStoreRequest $request): JsonResponse
    {
        try {
            $storeResponse = $this->departmentService->store($request);
            return $this->storeResponseHandler->handleStoreResponse($storeResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified department in storage.
     *
     * @param  \App\Http\Requests\Department\DepartmentUpdateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(DepartmentUpdateRequest $request): JsonResponse
    {
        try {
            $updatedResponse = $this->departmentService->update($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified department from storage.
     *
     * @param  \App\Http\Requests\Department\DepartmentDeleteRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DepartmentDeleteRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->departmentService->delete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
