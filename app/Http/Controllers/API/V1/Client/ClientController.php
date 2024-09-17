<?php

namespace App\Http\Controllers\API\V1\Client;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Service\V1\Client\ClientService;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;
use App\Http\Requests\Client\ClientIdRequest;
use App\Http\Requests\Client\ClientNameRequest;
use App\Http\Requests\Client\CreateClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Service\V1\Client\ClientInterfaceService;

class ClientController extends Controller
{
    protected $clientService, $deleteResponseHandler, $updateResponseHandler, $storeResponseHandler, $fetchResponseHandler, $clientArchiveService;

    public function __construct(
        ClientInterfaceService $clientService,
        StoreResponse $storeResponseHandler,
        DeleteResponse $deleteResponseHandler,
        UpdateResponse $updateResponseHandler,
        FetchResponse $fetchResponseHandler,

    ) {
        $this->clientService = $clientService;
        $this->storeResponseHandler = $storeResponseHandler;
        $this->deleteResponseHandler = $deleteResponseHandler;
        $this->updateResponseHandler = $updateResponseHandler;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }
    /**
     * get all clients
     * @param Request $request
     * @var $request
     * @return JsonResponse
     */

    public function fetch(Request $request): JsonResponse
    {
        try {
            $query = $request->query('query');
            $fetchResponse = $this->clientService->fetch($query);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * store client information
     * @param Request $request
     * @var $request
     * @return JsonResponse
     */
    public function store(CreateClientRequest $request): JsonResponse
    {
        try {
            // Save the new client using the service
            $storeResponse = $this->clientService->store($request);
            return $this->storeResponseHandler->handleStoreResponse($storeResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * update client info
     * @param Request $request
     * @var $request
     * @return JsonResponse
     */
    public function update(UpdateClientRequest $request): JsonResponse
    {
        try {
            $updatedResponse = $this->clientService->update($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * delete clients
     * @param Request $request
     * @var $request , $clientId
     * @return JsonResponse
     */
    public function delete(ClientIdRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->clientService->delete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * search clients
     * @param Request $request
     * @var $request ,$clientId
     * @return JsonResponse
     */
    public function search(ClientNameRequest $request): JsonResponse
    {
        try {
            $fetchResponse = $this->clientService->search($request);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * search clients by id
     * @param Request $request
     * @var $request ,$clientId
     * @return JsonResponse
     */
    public function findById(ClientIdRequest $request): JsonResponse
    {
        try {
            $fetchResponse = $this->clientService->findByClientId($request);
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * get all users frim archive
     * @method get
     */
    public function archivesFetch(): JsonResponse
    {
        try {
            $fetchResponse =  $this->clientService->archivesFetch();
            // handle the response
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * unarchive client
     * @param ClientIdRequest $request
     * @var $request
     */
    public function archivesRestore(ClientIdRequest $request)
    {
        try {
            $deleteResponse = $this->clientService->archivesRestore($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * delete archived clients
     * @param Request $request
     * @var $request , $clientId
     * @return JsonResponse
     */
    public function archivesDelete(ClientIdRequest $request): JsonResponse
    {
        try {
            // handle the response
            $deleteResponse = $this->clientService->archivesDelete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * search archived clients
     * @param Request $request
     * @var $request , $clientId
     * @return JsonResponse
     */
    public function archivesSearch(ClientNameRequest $request): JsonResponse
    {
        try {
            // handle client archives search response
            $searchResponse = $this->clientService->archivesSearch($request);
            return $this->fetchResponseHandler->handlefetchResponse($searchResponse);
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}