<?php

namespace App\Service\V1\Client;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Client\FetchClients;
use App\Repository\V1\Client\ClientInterfaceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ClientService implements ClientInterfaceService
{

    protected $clientRepository;
    /**
     * ClientArchiveService Constructor.
     *
     * This constructor initializes the ClientArchiveService object.
     * It takes an instance of the ClientInterfaceRepository class as a dependency injection.
     *
     * @param ClientInterfaceRepository $clientRepository
     *     An instance of the ClientRepository class, providing data access methods for client-related operations.
     *     This instance will be injected into the ClientArchiveService.
     */
    public function __construct(ClientInterfaceRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    /**
     * Retrieve information about clients.
     *
     * @return array
     */
    public function clientInfo() : array
    {
        try {
            $clients = $this->clientRepository->clientInfo();

            return [
            'status' => JsonResponse::HTTP_OK,
            'data' => [
            'totalNumberOfClients' => $clients['totalNumberOfClients'],
            'type' => $clients['type'],
            'percentage' => $clients['percentage']
                ],
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Internal server error. Please try again later.',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Fetch all clients.
     *
     * @param $query string
     * @return array
     */
    public function fetch($query): array
    {
        try {
            // Retrieve all clients from the repository
            $clients = $this->clientRepository->fetch($query);

            return [
                'clients' => FetchClients::collection(collect($clients)->unique('name')),
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
     * store client
     * @param $request
     * @return array
     */
    public function store($request): array
    {

        try {
            // Retrieve validated input / pop to to array
            $cleanData = $request->validated();
            $name = $cleanData['name'];

            $results = $this->clientRepository->findClientByName($cleanData['name']);
            if (!empty($results)) {
                return [
                    'message' => 'Client name already exist, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }
            // Construct $clientData as an associative array
            $clientData = [
                'clientId' => Str::uuid(),
                'name' => $name,
                'details' => $cleanData['details'],
                'createdBy' => $request->user()->employee->id,
            ];

            //pass the data to repository
            $client =   $this->clientRepository->save($clientData);

            if (!$client) {
                return [
                    'message' => 'Failed to create client, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Client created successfully.',
                'client' => new FetchClients($client),
                'status' => JsonResponse::HTTP_OK,
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
     * delete a client
     * @param $request
     * @return array
     */
    public function delete($request): array
    {

        try {
            // Retrieve validated input / pop to to array
            $cleanData = $request->validated();


            $checkClient = $this->clientRepository->findByClientId($cleanData['clientId']);

            if (!$checkClient) {
                return [
                    'message' => 'Client does not exist, please try again.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            //pass the data to repository
            //boolean return
            $delete = $this->clientRepository->deleteByClientId($cleanData['clientId']);
            if (!$delete) {
                return [
                    'message' => 'Client deleting was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }
            return [
                'message' => 'Client archived successfully.',
                'status' => JsonResponse::HTTP_OK,
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
     * update client data
     * @param $request
     * @return array
     */
    public function update($request): array
    {
        try {
            $cleanData = $request->validated();

            $checkClient = $this->clientRepository->findByClientId($cleanData['clientId']);
            $name = $cleanData['name'];

            if (!$checkClient) {
                return [
                    'message' => 'Client does not exist, please try again.',
                    'status' => JsonResponse::HTTP_NOT_FOUND,
                ];
            }

            $clientData = [
                'clientId' => $cleanData['clientId'],
                'name' => $name,
                'details' => $cleanData['details'],
            ];

            $clients = $this->clientRepository->updateByClientId($clientData);
            if (!$clients) {
                throw new ModelNotFoundException();
            }

            return [
                'message' => 'client updated successfully.',
                'client' => FetchClients::collection($clients),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'client update was not successful, please try again.',
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


    public function search($request): array
    {
        try {
            $cleanData = $request->validated();
            // Perform the search based on the search parameter
            $results = $this->clientRepository->findClientByName($cleanData['name']);

            if (empty($results)) {
                throw new ModelNotFoundException();
            }

            // Return the search results
            return [
                "clients" => new FetchClients($results),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    public function findByClientId($request): array
    {
        try {
            $cleanData = $request->validated();
            // Perform the search based on the search parameter
            $results = $this->clientRepository->findByClientId($cleanData['clientId']);

            if (empty($results)) {
                throw new ModelNotFoundException();
            }

            // Return the search results
            return [
                "client" => new FetchClients($results),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            // User not found
            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    //archive operations
        /**
     * Fetch all archived clients.
     *
     * @return array
     */
    public function archivesFetch()
    {
        try {
            $archivedClients = $this->clientRepository->fetchArchives();

            return [
                'archives' => FetchClients::collection(collect($archivedClients)->unique('name')),
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Archived clients fetching was not successful, please try again.',
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
     * Unarchive a client.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function archivesRestore($request)
    {
        try {
            $unArchive = $this->clientRepository->restoreArchive($request->clientId);

            if (!$unArchive) {
                return [
                    'message' => 'Client archive retored was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Client archive retored successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Client archive retored was not successful, please try again.',
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
     * delete archived clients
     * @param $request
     * @var $clientIdRequest $request
     * Delete archived clients.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function archivesDelete($request)
    {
        try {
            $delete = $this->clientRepository->deleteArchive($request->clientId);

            if (!$delete) {
                return [
                    'message' => 'Archived client deletion was not successful, please try again.',
                    'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
                ];
            }

            return [
                'message' => 'Client archive deleted successfully.',
                'status' => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'message' => 'Archived client deletion was not successful, please try again.',
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
     * Search archived clients.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function archivesSearch($request)
    {
        try {
            $archivedClients = $this->clientRepository->searchByNameOrCode($request->name);

            return [
                "results" =>  FetchClients::collection(collect($archivedClients)->unique('name')),
                "status" => JsonResponse::HTTP_OK,
            ];
        } catch (ModelNotFoundException $e) {
            return [
                'results' => [],
                'status' => JsonResponse::HTTP_NOT_FOUND,
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