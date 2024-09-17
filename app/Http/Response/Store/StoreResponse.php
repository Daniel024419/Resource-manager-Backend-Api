<?php

namespace App\Http\Response\Store;
use Illuminate\Http\JsonResponse;

class  StoreResponse
{
  /**
     * Handle store response and return the appropriate JSON response.
     *
     * @param array $storeResponse
     * @return JsonResponse
     */

     public function handleStoreResponse(array $storeResponse): JsonResponse
     {
         if ($storeResponse) {
             switch ($storeResponse['status']) {
                 case '200':
                     
                     return response()->json($storeResponse, JsonResponse::HTTP_OK);
                     break;

                 case '201':
                     
                     return response()->json($storeResponse, JsonResponse::HTTP_OK);
                     break;

                 case '412':
                     
                     return response()->json($storeResponse, JsonResponse::HTTP_PRECONDITION_FAILED);
                     break;

                 case '404':
                     
                     return response()->json($storeResponse, JsonResponse::HTTP_NOT_FOUND);
                     break;

                 case '400':

                    return response()->json($storeResponse,JsonResponse::HTTP_BAD_REQUEST);
                    break;    

                 case '500':
                     
                     return response()->json($storeResponse, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                     break;

                 default:
                     return response()->json(
                         ['error' => 'An unknown error occurred. Try saving  again.'],
                         JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                     );
                     break;
             }
         }

         // Handle other cases if needed
         return response()->json(
             ['error' => 'An unknown error occurred, Try saving  again.'],
             JsonResponse::HTTP_INTERNAL_SERVER_ERROR
         );
     }
}