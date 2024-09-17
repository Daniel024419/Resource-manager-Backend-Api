<?php

namespace App\Http\Response\Delete;

use Illuminate\Http\JsonResponse;

class  DeleteResponse
{
    /**
     * Handle the delete response and return the appropriate JSON response.
     *
     * @param array $storeResponse
     * @return JsonResponse
     */

     public function handleDeleteResponse(array $deleteResponse): JsonResponse
     {
         if ($deleteResponse) {
             switch ($deleteResponse['status']) {
                 case '200':
                     
                     return response()->json($deleteResponse, JsonResponse::HTTP_OK);
                     break;

                 case '412':
                     
                     return response()->json($deleteResponse, JsonResponse::HTTP_PRECONDITION_FAILED);
                     break;

                 case '404':
                     
                     return response()->json($deleteResponse, JsonResponse::HTTP_NOT_FOUND);
                     break;

                 case '500':
                     
                     return response()->json($deleteResponse, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
                     break;

                 default:
                     return response()->json(
                         ['error' => 'An unknown error occurred. Try deleting again.'],
                         JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                     );
                     break;
             }
         }

         // Handle other cases if needed
         return response()->json(
             ['error' => 'An unknown error occurred, Try deleting again.'],
             JsonResponse::HTTP_INTERNAL_SERVER_ERROR
         );
     }
}
