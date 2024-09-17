<?php

namespace App\Http\Controllers\API\V1\Skills;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Service\V1\Skills\SkillsInterfaceService;
use App\Http\Requests\Skills\SkillsfetchRequest;
use App\Http\Requests\Skills\SkillsStoreRequest;
use App\Http\Requests\Skills\SkillsDeleteRequest;
use App\Http\Requests\Skills\SkillsUpdateRequest;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;

class SkillsController extends Controller
{
    protected $skillsService, $deleteResponseHandler, $updateResponseHandler, $storeResponseHandler, $fetchResponseHandler;

    /**
     * @param SkillsService $skillsService
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
        SkillsInterfaceService $skillsService,
        StoreResponse $storeResponseHandler,
        DeleteResponse $deleteResponseHandler,
        UpdateResponse $updateResponseHandler,
        FetchResponse $fetchResponseHandler
    ) {
        $this->skillsService = $skillsService;
        $this->storeResponseHandler = $storeResponseHandler;
        $this->deleteResponseHandler = $deleteResponseHandler;
        $this->updateResponseHandler = $updateResponseHandler;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }

    /**
     * fetch all Skillss
     *
     * @param Request $request
     * @return JsonResponse
     *
     * */
    public function fetch(): JsonResponse
    {
        try {
            $fetchResponse = $this->skillsService->fetch();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //fetchByAuth
    /**
     * fetch all Skillss by auth
     *
     * @param Request $request
     * @return JsonResponse
     *
     * */
    public function fetchByAuth(Request $request): JsonResponse
    {
        try {
            $fetchResponse = $this->skillsService->fetchByAuth($request);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created skills in storage.
     *
     * @param  \App\Http\Requests\Skills\SkillsStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * */
    public function store(SkillsStoreRequest $request): JsonResponse
    {
        try {
            $storeResponse = $this->skillsService->store($request);
            return $this->storeResponseHandler->handleStoreResponse($storeResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified skills in storage.
     *
     * @param  \App\Http\Requests\Skills\SkillsUpdateRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * */
    public function update(SkillsUpdateRequest $request): JsonResponse
    {
        try {
            $updatedResponse = $this->skillsService->update($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified skills from storage.
     *
     * @param  \App\Http\Requests\Skills\SkillsDeleteRequest  $request
     * @return \Illuminate\Http\JsonResponse
     * */
    public function delete(SkillsDeleteRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->skillsService->delete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}