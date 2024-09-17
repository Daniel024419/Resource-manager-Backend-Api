<?php


namespace App\Http\Response\Fetch;

use Illuminate\Http\JsonResponse;

class FetchResponse // Corrected class name capitalization
{
    /**
     * Handle the  fetch response and return the appropriate JSON response.
     *
     * @param array $fetchResponse
     * @return JsonResponse
     */
    public function handlefetchResponse(array $fetchResponse): JsonResponse
    {

        if ($fetchResponse) {
            switch ($fetchResponse['status']) {
                case '200':
                    
                    return response()->json($fetchResponse, JsonResponse::HTTP_OK);
                    break;

                case '404':
                    
                    return response()->json($fetchResponse, JsonResponse::HTTP_NOT_FOUND);
                    break;

                case '412':
                    
                    return response()->json($fetchResponse, JsonResponse::HTTP_PRECONDITION_FAILED);
                    break;

                case '401':
                    
                    return response()->json($fetchResponse, JsonResponse::HTTP_UNAUTHORIZED);
                    break;

                case '500':
                    
                    return response()->json($fetchResponse, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                    break;

                default:
                    // Handle other cases if needed
                    return response()->json(
                        ['error' => 'An unknown error occurred. Try again.'],
                        JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                    );
                    break;
            }
        }

        // Handle other cases if needed
        return response()->json(
            ['error' => 'An unknown error occurred, Try again.'],
            JsonResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}