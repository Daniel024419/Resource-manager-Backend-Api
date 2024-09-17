<?php

namespace App\Http\Controllers\API\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Response\Fetch\FetchResponse;
use App\Service\V1\Auth\AuthInterfaceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{

    /**
     * UserController Constructor.
     *
     * This constructor is responsible for initializing the AuthController object.
     * It takes an instance of the AuthService class as a dependency injection.
     *
     * @param AuthInterfaceService $authService
     *     An instance of the AuthService class, which provides functionality related
     *     to user-related operations. This instance will be injected into the AuthController.
     * @param FetchResponse $fetchResponseHandler
     */
    protected $authService, $fetchResponseHandler;

    public function __construct(
        AuthInterfaceService $authService,
        FetchResponse $fetchResponseHandler
    ) {
        $this->authService = $authService;
        $this->fetchResponseHandler = $fetchResponseHandler;
    }
    /**
     * login user
     * @param Request $request
     * @var < email ,password >
     * @return JsonResponse
     */

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // Retrieve validated input directly from the request
            $credentials = $request->validated();
            // Use the AuthService to handle authentication logic
            $authResponse = $this->authService->authenticateUser($credentials, $request);
            // Use the fetchResponseHandler to handle the response
            return $this->fetchResponseHandler->handlefetchResponse($authResponse);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * logout users
     * @method post
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Use the logout to handle logout
            $logoutResponse =  $this->authService->logout($request);
            // Use the fetchResponseHandler to handle the response
            return $this->fetchResponseHandler->handlefetchResponse($logoutResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /***
     * token exchange
     * @method get
     * @param Request $request
     * @return JsonResponse
     */
    public function tokenExhange(Request $request): JsonResponse
    {
        try {
            $exchangeResponse =  $this->authService->tokenExchange($request);
            // Use the fetchResponseHandler to handle the response
            return $this->fetchResponseHandler->handlefetchResponse($exchangeResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}