<?php

namespace App\Repository\V1\Client;

use Exception;
use Carbon\Carbon;
use App\Models\V1\Client\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ClientRepository implements ClientInterfaceRepository
{
    /**
     * Retrieve information about clients.
     *
     * @return array
     */
    public function clientInfo(): array
    {
        $clients = Client::withTrashed()->get();
        $clientOverView =  $this->clientOverView($clients);
        
        return [
            'totalNumberOfClients' => $clientOverView['totalNumberOfClients'],
            'type' =>$clientOverView['type'],
            'percentage' =>$clientOverView['percentage'],
        ];
    }
    
    /** 
    *generate clients overview within a specific month
    *@return array
    */
    private function clientOverView($clients){
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $percentageCurrentMonth = 0;
        $percentagePreviousMonth = 0;
        $type = "";
        
        $currentMonthsCounts = $clients->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])->count();
        $previousMonthsCounts = $clients->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])->count();
        $totalClientsCount = $clients->count();
        if($totalClientsCount > 0) {
        $percentageCurrentMonth = ($currentMonthsCounts / $totalClientsCount) * 100;
        $percentagePreviousMonth = ($previousMonthsCounts / $totalClientsCount) * 100;
        }
        if($currentMonthsCounts > $previousMonthsCounts){
            $type = "Increased";
        }
        else if($currentMonthsCounts == $previousMonthsCounts){
           $type = "Stable";
           
        }else{
            $type = "Decreased";
        }

        return[
            'totalNumberOfClients' => $totalClientsCount,
            'type' =>$type,
            'percentage' =>round( abs($percentageCurrentMonth - $percentagePreviousMonth),1),
        ];
    }

    /**
     * Save a new client with an array of data.
     *
     * @param array $clientData
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function save(array $clientData)
    {
        try {
            DB::beginTransaction();
            $clientId = $clientData['clientId'];
            // Create a new Client instance and save it
            $client =   Client::create($clientData);

            // Client::where('clientId', $clientId)->get();
            DB::commit();
            return $client;  // Return the created client
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            // Throw an exception if model is not found
            throw new ModelNotFoundException();
        }
    }

    /**
     * Fetch all clients.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetch($query): Collection | null
    {
        try {
            $searchQuery = strtoupper($query);
            if ($searchQuery) {
                $clients = Client::where('name', 'ILIKE', "%$searchQuery%")
                    ->orWhere('details', 'ILIKE', "%$searchQuery%")
                    ->orderBy('name', 'asc')->get();
                return $clients;
            }
            // Fetch all clients if query is empty
            $clients = Client::orderBy('name', 'asc')->get();
            return $clients;
        } catch (Exception $e) {
            // Handle exceptions and return null
            return null;
        }
    }

    /**
     * Delete a client by clientId.
     *
     * @param string $clientId
     * @return bool
     */
    public function deleteByClientId($clientId): bool
    {
        try {
            DB::beginTransaction();
            // Delete a client by clientId
            Client::where('clientId', $clientId)->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Find a client by clientId.
     *
     * @param string $clientId
     * @return Client|null
     */
    public function findByClientId($clientId) : Client | null
    {
        try {
            // Find a client by clientId
            $client = Client::where('clientId', $clientId)->first();
            return $client;
        } catch (Exception $e) {
            // Handle exceptions and return an empty array
            return null;
        }
    }

    /**
     * Find a client by client name.
     *
     * @param string $name
     * @return Client|null
     */
    public function findClientByName($name) : Client | null
    {
        try {
            // Find a client by client name
            $client = Client::whereRaw('LOWER(name) = ?', strtolower($name))->first();
            return $client;
        } catch (Exception $e) {
            // Log or handle the exception as needed
            return null;
        }
    }

    /**
     * Update a client by clientId.
     *
     * @param array $clientData
     * @return mixed
     */
    public function updateByClientId(array $clientData)
    {
        try {
            DB::beginTransaction();

            $clientId = $clientData['clientId'];

            // Remove id before update
            unset($clientData['clientId']);

            // Find client instance and update it
            $store = Client::where('clientId', $clientId)->update($clientData);

            if (!$store) {
                DB::rollBack();
                return false;
            }
            // Retrieve the updated client
            $client = Client::where('clientId', $clientId)->get();
            DB::commit();
            return $client;
        } catch (ModelNotFoundException $e) {
            DB::rollBack();

            return false;
        }
    }


    // Archive operations

    /**
     * Fetch only archived clients.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function fetchArchives(): Collection| null
    {
        try {
            $archivedClients = Client::onlyTrashed()->get();
            return $archivedClients;
        } catch (Exception $e) {
            // Handle exceptions and return null
            return null;
        }
    }

    /**
     * Restore a soft-deleted (archived) client by clientId.
     *
     * @param int $clientId
     * @return bool
     */
    public function restoreArchive($clientId): bool
    {
        try {
            DB::beginTransaction();
            // Restore a soft-deleted (archived) client by clientId
            Client::withTrashed()->where('clientId', $clientId)->restore();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Permanently delete a soft-deleted (archived) client by clientId.
     *
     * @param int $clientId
     * @return bool
     */
    public function deleteArchive($clientId): bool
    {
        try {
            DB::beginTransaction();
            // Permanently delete a soft-deleted (archived) client by clientId
            Client::where('clientId', $clientId)->forceDelete();
            DB::commit();
            return true;
        } catch (Exception $e) {

            DB::rollBack();
            // Handle exceptions and return false
            return false;
        }
    }

    /**
     * Search for archived clients by name or project code.
     *
     * @param string $nameOrCode
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function searchByNameOrCode($nameOrCode): Collection | array
    {
        try {
            // Search for archived clients by name or project code
            $archivedClients = Client::withTrashed()
                ->where('name', 'LIKE', '%' . $nameOrCode . '%')
                ->get();
            return $archivedClients;
        } catch (Exception $e) {
            // Handle exceptions and return an empty array
            return [];
        }
    }

    /**
     * find archived clients by id
     *
     * @param mixed $nameOrCode
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function findArchiveByClientId($clientId): Collection | null
    {
        try {
            $client = Client::withTrashed()->where('clientId', $clientId)->first();
            return $client;
        } catch (Exception $e) {

            return null;
        }
    }

    /**
     * Generate clients reports.
     *
     * @return \Illuminate\Database\Eloquent\Collection|null
     */
    public function clientReports()

    {
        try {
            $clients = Client::with(['projects.employeeProjects' => function ($query) {
                $query->withTrashed();
            }])->withTrashed()->get();
            return $clients;
        } catch (Exception $e) {
            // Handle exceptions and return null
            return null;
        }
    }
}