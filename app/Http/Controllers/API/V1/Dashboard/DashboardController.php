<?php

namespace App\Http\Controllers\API\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Service\V1\Client\ClientService;
use App\Service\V1\Project\ProjectService;
use App\Service\V1\TimeOff\TimeOffServiceInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param TimeOffServiceInterface $timeOffService
     * @param ProjectService $projectService
     * @param ClientService $clientService
     */
    public function __construct(
        public TimeOffServiceInterface $timeOffService,
        public ProjectService $projectService,
        public ClientService $clientService
    ) {
    }

    /**
     * Retrieve information about time offs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeOffInfo()
    {
        try {
            $response = $this->timeOffService->timeOffInfo();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            Log::info($e);
        }
    }

    /**
     * Retrieve information about time offs requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeOffRequest()
    {
        try {
            $response = $this->timeOffService->timeOffRequest();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            Log::info($e);
        }
    }

    /**
     * Retrieve information about projects.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function projectInfo()
    {
        try {
            $response = $this->projectService->projectInfo();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            Log::info($e);
        }
    }

    /**
     * Retrieve information about clients.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clientInfo()
    {
        try {
            $response = $this->clientService->clientInfo();

            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            Log::info($e);
        }
    }
}
