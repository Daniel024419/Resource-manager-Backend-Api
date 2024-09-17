<?php

namespace App\Exceptions;

use BadMethodCallException;
use Throwable;
use RuntimeException;
use Dotenv\Exception\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{

    /**
     * Register the exception handling callbacks for the application.
     */

    public function register(): void
    {


        //not found
        $this->renderable(function (NotFoundHttpException $error) {
            return response()->json([
                'message' => 'The resource you require may have been moved to different place',
                'error' => $error->getMessage()
            ], 404);
        });

        $this->renderable(function (RuntimeException $error) {

            //runtime
            return response()->json([
                'message' => 'Server may be busy or not responding to request now , Please, Try again sometime.',
                'error' => $error->getMessage(),
            ], 500);
        });


        $this->renderable(function (AuthenticationException $error) {
            return response()->json([
                'message' => 'Only authenticated users are allowed to perform this action',
                'accessToken' => 'Missing',
                'error' => $error->getMessage(),
            ], 401);
        });


        $this->renderable(function (ValidationException $error) {
            return response()->json([
                'message' => 'Server cannot process the provided request',
                'error' => $error->getMessage(),
            ], 422);
        });



        $this->renderable(function (ModelNotFoundException $error) {
            return response()->json([
                'message' => 'Resource not found ,Try again',
                'error' => $error->getMessage(),
            ], 404);
        });


        // $this->renderable(function ( $error) {

        //          return response()->json([
        //             'error' => $error->getMessage(),
        //             'message' => 'Invalid credentials',
        //         ], 400);

        // });


        $this->renderable(function (BadRequestException $error) {

            return response()->json([
               'error' => $error->getMessage(),
               'message' => 'Invalid request',
        ], 412);
        });


        $this->renderable(function (BadMethodCallException $error) {

            return response()->json([
               'error' => $error->getMessage(),
               'message' => 'Invalid method call',
        ], 412);
        });





    }


}