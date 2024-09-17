<?php

namespace App\Service\V1\Client;

/**
 * Interface ClientInterfaceService
 * @package App\Service\V1\Client
 */
interface ClientInterfaceService
{
    /**
     * Retrieve information about clients.
     *
     * @return array
     */
    public function clientInfo() : array;

    /**
     *save new client
     * @return array
     */
    public function store($clientData) : array;

    /**
     * @param $request
     * @return array users
     */
    public function fetch($query): array;

    /**
     * delete a client
     * @param $request
     * @return array
     */

    public function delete($request): array;

    /**
     * search clients
     * @param Request $request
     * @var $request ,$clientId
     * @return array
     */
    public function search($request): array;

    /**
     * Finds a client by their unique client ID.
     *
     * @param mixed $clientId The unique identifier of the client to be retrieved.
     * @return array|null Returns the client if found, or null if not found.
     */
    public function findByClientId($clientId):array;


    /**
     * update client data
     * @param $request
     * @return array
     */
    public function update($request);

    //archives operations
    /**
     * Fetch archived clients.
     *
     * @return mixed
     */
    public function archivesFetch();

    /**
     * Unarchive a client.
     *
     * @param mixed $request
     */
    public function archivesRestore($request);

    /**
     * Delete archived clients.
     *
     * @param mixed $request
     * @return mixed
     */
    public function archivesDelete($request);

    /**
     * Search archived clients.
     *
     * @param mixed $request
     * @return mixed
     */
    public function archivesSearch($request);
}