<?php

namespace App\Http\Controllers\API\V1\Reports;

use Exception;
use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Service\V1\Reports\ReportServiceInterfaceService;
use App\Http\Response\Fetch\FetchResponse;

class ReportController extends Controller
{
    protected  $reportService, $fetchResponseHandler;
    public function __construct(
        ReportServiceInterfaceService $reportService,
        FetchResponse $fetchResponseHandler,
    ) {
        $this->reportService = $reportService;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }



    /**
     * get all basic user reports on project
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function basicUser(): JsonResponse
    {
        try {
            $fetchResponse = $this->reportService->basicUser();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * get all  reports on clients
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function clients(): JsonResponse
    {
        try {
            $fetchResponse = $this->reportService->clients();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * get all  reports on projects
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function projects(): JsonResponse
    {
        try {
            $fetchResponse = $this->reportService->projects();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * get all utilization reports
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function utilization(): JsonResponse
    {
        try {
            $fetchResponse = $this->reportService->utilization();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * get all  reports on time off
     * @var $request
     * @method get
     * @return JsonResponse
     */
    public function timeOff(): JsonResponse
    {
        try {
            $fetchResponse = $this->reportService->timeOff();
            return $this->fetchResponseHandler->handlefetchResponse($fetchResponse);
        } catch (RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}