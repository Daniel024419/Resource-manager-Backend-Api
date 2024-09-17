<?php

namespace App\Http\Controllers\API\V1\Notification;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Service\V1\Auth\AuthService;
use App\Http\Response\Fetch\FetchResponse;
use App\Service\V1\Notification\NotificationInterfaceService;
use App\Http\Requests\Notification\NotificationRequest;

class NotificationController extends Controller
{
    protected $authService, $fetchResponseHandler, $notificationsService;

    public function __construct(
        AuthService $authService,
        FetchResponse $fetchResponseHandler,
        NotificationInterfaceService $notificationService
    ) {
        // Dependency injection for AuthService and AuthResponseHandler
        $this->authService = $authService;
        $this->fetchResponseHandler = $fetchResponseHandler;
        $this->notificationsService = $notificationService;
    }

    /**
     * Token exchange method for notifications by user.
     *
     * @return JsonResponse | array
     */
    public function fetch(): JsonResponse | array
    {
        try {
            // Use the AuthService to handle token exchange for notifications
            $exchangeResponse = $this->authService->tokenExchangeForNotification();
            // Use the AuthResponseHandler to handle the response
            return $this->fetchResponseHandler->handlefetchResponse($exchangeResponse);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mark all notifications as read for a user.
     * @return JsonResponse | array
     */
    public function markAllAsRead(): JsonResponse | array
    {
        try {
            return $this->notificationsService->markAllAsRead();
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        // Return a JsonResponse indicating the success or failure of the operation
    }


    /**
     * Mark a notifications as read for a user.
     *
     * @param Request $request
     * @return JsonResponse | array
     */
    public function markOneAsRead(NotificationRequest $request): JsonResponse | array
    {
        try {
            return $this->notificationsService->markOneAsRead($request);
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the process
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        // Return a JsonResponse indicating the success or failure of the operation
    }
}
