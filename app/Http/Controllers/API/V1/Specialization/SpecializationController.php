<?php

namespace App\Http\Controllers\API\V1\Specialization;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;
use App\Http\Requests\Specialization\SpecializationStoreRequest;
use App\Http\Requests\Specialization\SpecializationDeleteRequest;
use App\Http\Requests\Specialization\SpecializationUpdateRequest;
use App\Service\V1\Specialization\SpecializationInterfaceService;

class SpecializationController extends Controller
{

    protected $specializationService, $deleteResponseHandler, $updateResponseHandler, $storeResponseHandler, $fetchResponseHandler;
    /**
     * Constructor for the SpecializationController.
     *
     * @param SpecializationInterfaceService $specializationService
     *     The service responsible for handling specialization-related operations.
     * @param StoreResponse $storeResponseHandler
     *     The response handler for store (create) operations.
     * @param DeleteResponse $deleteResponseHandler
     *     The response handler for delete operations.
     * @param UpdateResponse $updateResponseHandler
     *     The response handler for update operations.
     * @param FetchResponse $fetchResponseHandler
     *     The response handler for fetch (get) operations.
     */

    public function __construct(
        SpecializationInterfaceService $specializationService,
        StoreResponse $storeResponseHandler,
        DeleteResponse $deleteResponseHandler,
        UpdateResponse $updateResponseHandler,
        FetchResponse $fetchResponseHandler
    ) {
        $this->specializationService = $specializationService;
        $this->storeResponseHandler = $storeResponseHandler;
        $this->deleteResponseHandler = $deleteResponseHandler;
        $this->updateResponseHandler = $updateResponseHandler;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }

    public function getASpecilization($specialization)
    {
        try {
            $specializationData = $this->specializationService->getASpecilization($specialization);
            return $this->fetchResponseHandler->handlefetchResponse($specializationData);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * fetch all Specializations
     *
     * @return JsonResponse
     *
     * */
    public function fetch(): JsonResponse
    {
        try {
            $fetchResponse = $this->specializationService->fetch();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created specialization in storage.
     *
     * @param  \App\Http\Requests\Specialization\SpecializationStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * */
    public function store(SpecializationStoreRequest $request): JsonResponse
    {
        try {
            $storeResponse = $this->specializationService->store($request);
            return $this->storeResponseHandler->handleStoreResponse($storeResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified specialization in storage.
     *
     * @param  \App\Http\Requests\Specialization\SpecializationUpdateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * */
    public function update(SpecializationUpdateRequest $request): JsonResponse
    {
        try {
            $updatedResponse = $this->specializationService->update($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified specialization from storage.
     *
     * @param  \App\Http\Requests\Specialization\SpecializationDeleteRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * */
    public function delete(SpecializationDeleteRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->specializationService->delete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
