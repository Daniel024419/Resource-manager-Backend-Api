<?php

namespace App\Http\Response\Update;

use Illuminate\Http\JsonResponse;
use App\Http\Resources\Auth\AuthServiceInvalidResource;

class UpdateResponse
{

    /**
     * Handles the update response
     *
     * @param array $updatedResponse
     * @return JsonResponse The response to be sent to the client.
     */
    public function handleUpdateResponse($updatedResponse): JsonResponse // Corrected method name capitalization
    {
        if ($updatedResponse) {
            switch ($updatedResponse['status']) {
                case '412':

                    return response()->json($updatedResponse, JsonResponse::HTTP_PRECONDITION_FAILED);
                    break;

                case '406':

                    return response()->json($updatedResponse, JsonResponse::HTTP_NOT_ACCEPTABLE);
                    break;

                case '417':

                    return response()->json($updatedResponse, JsonResponse::HTTP_EXPECTATION_FAILED);
                    break;

                case '200':

                    return response()->json($updatedResponse, JsonResponse::HTTP_OK);
                    break;

                    // Handle case '404'
                case '404':

                    return response()->json(
                        new AuthServiceInvalidResource($updatedResponse),
                        JsonResponse::HTTP_NOT_FOUND
                    );
                    break;

                case '400':

                    return response()->json(
                        new AuthServiceInvalidResource($updatedResponse),
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                    break;

                    // Handle case '422'
                case '422':

                    return response()->json(
                        new AuthServiceInvalidResource($updatedResponse),
                        JsonResponse::HTTP_UNPROCESSABLE_ENTITY
                    );
                    break;

                case '202':

                    return response()->json($updatedResponse, JsonResponse::HTTP_ACCEPTED);
                    break;

                case '500':

                    return response()->json($updatedResponse, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);

                default:
                    return response()->json(['error' => 'Unexpected update response'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // If the update response is empty, return an error
        return response()->json(
            ['error' => 'Unexpected update response'],
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
