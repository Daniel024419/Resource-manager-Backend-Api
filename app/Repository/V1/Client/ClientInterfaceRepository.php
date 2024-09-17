<?php

namespace App\Repository\V1\Client;

use App\Models\V1\Client\Client;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface for Client Repository.
 */
interface ClientInterfaceRepository
{
    /**
     * Retrieve information about clients.
     *
     * @return array
     */
    public function clientInfo(): array;
    /**
     * Save a new client.
     *
     * @param array $clientData
     * @return mixed
     */
    function save(array $clientData);

    /**
     * Fetch all clients.
     *
     * @return Collection | null
     */
    public function fetch($query): Collection | null;

    /**
     * Delete a client by client ID.
     *
     * @param mixed $clientId
     * @return bool
     */
    public function deleteByClientId($clientId): bool;

    /**
     * Find a client by client ID.
     *
     * @param mixed $clientId
     * @return Client | null
     */
    public function findByClientId($clientId) : Client | null;

     /**
     * Find a client by client name.
     *
     * @param string $name
     * @return Client|null
     */
    public function findClientByName($name) : Client | null;

    /**
     * Update a client by client ID.
     *
     * @param array $clientData
     * @return mixed
     */
    public function updateByClientId(array $clientData);

    // Archive operations

    /**
     * Fetch all archived clients.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetchArchives(): Collection | null;

    /**
     * delete archive a client by client ID.
     *
     * @param mixed $clientId
     * @return bool
     */
    public function deleteArchive($clientId): bool;

    /**
     * Restore a client from the archive by client ID.
     *
     * @param mixed $clientId
     * @return bool
     */
    public function restoreArchive($clientId): bool;

    /**
     * Search archived clients by name or code.
     *
     * @param mixed $nameOrCode
     * @return Collection | array
     */
    public function searchByNameOrCode($nameOrCode) : Collection | array;


    /**
     * find archived clients by id.
     *
     * @param mixed $nameOrCode
     * @return mixed
     */
    public function findArchiveByClientId($clientId);

    /**
     * Generate clients reports.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function clientReports();
}


