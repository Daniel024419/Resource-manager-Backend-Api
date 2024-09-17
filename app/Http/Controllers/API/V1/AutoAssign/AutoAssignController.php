<?php

namespace App\Http\Controllers\API\V1\AutoAssign;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutoAssign\AutoAssignRequest;
use App\Models\V1\Project\Project;
use App\Service\V1\AutoAssign\AutoAssignServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoAssignController extends Controller
{
    //
    public function __construct(public AutoAssignServiceInterface $autoAssignService)
    {
    }

    public function autoAssign(Project $project, AutoAssignRequest $request)
    {
        try {
            $response = $this->autoAssignService->autoAssign($project,$request->all());
            return response()->json($response, $response['status']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
