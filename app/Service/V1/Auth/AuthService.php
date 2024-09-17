<?php

namespace App\Service\V1\Auth;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Employee\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Service\V1\Users\UserService;

use App\Http\Resources\Auth\LoginResource;
use App\Service\V1\Employee\EmployeeService;
use Illuminate\Auth\AuthenticationException;

use App\Repository\V1\Auth\AuthInterfaceRepository;
use App\Repository\V1\Users\UserInterfaceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Notification\NotificationRepository;
use App\Http\Resources\Notification\FetchNotificationResource;

class AuthService implements AuthInterfaceService
{

    public $authRepository, $userService, $employeeService, $notificationRepository, $userRepository;

    /**
     * AuthService Constructor.
     *
     * This constructor initializes the AuthService object, implementing the AuthInterfaceService.
     * It takes instances of repositories and services as dependencies for authentication-related operations.
     *
     * @param AuthInterfaceRepository $authRepository
     *     An instance of AuthInterfaceRepository providing data access methods for authentication.
     *
     * @param UserService $userService
     *     An instance of UserService providing functionality related to user operations.
     *
     * @param EmployeeService $employeeService
     *     An instance of EmployeeService providing functionality related to employee operations.
     *
     * @param NotificationRepository $notificationRepository
     *     An instance of NotificationRepository providing data access methods for notifications.
     */

    public function __construct(
        AuthInterfaceRepository $authRepository,
        UserService $userService,
        EmployeeService $employeeService,
        NotificationRepository $notificationRepository,
        UserInterfaceRepository $userRepository,
    ) {
        $this->authRepository = $authRepository;
        $this->userService = $userService;
        $this->employeeService = $employeeService;
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Find a user by email or username
     *
     * @param string $email
     * @return array
     */
    public function findByEmail(string $email)
    {
        try {
            $response = $this->authRepository->findByEmail($email);
            return $response;
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }


    /**
     * Authenticate a user with the provided credentials.
     *
     * @param array $credentials
     * @return array
     * @throws ModelNotFoundException
     */

    public function authenticateUser(array $credentials, $request): array
    {
        try {
            $credentials = $request->validated();

            $user = $this->authRepository->findByEmail($credentials['email']);

            if (!empty($user['deleted_at'])) {
                throw new ModelNotFoundException("User account is archived, please contact your administrator");
            }

            if (Auth::guard('web')->attempt(['email' => $credentials['email'], 'password' => $credentials['password']], (bool)$request->remember_token)) {

                $request->user()->tokens()->where('name', $credentials['email'])->delete();

                $token = $this->userRepository->find($user['id'])->createToken($credentials['email'], ['*'], now()->addDay())->plainTextToken;
                return [
                    'message' => 'Login successfully',
                    'accessToken' => $token,
                    'user' => new LoginResource($user),
                    'status' => JsonResponse::HTTP_OK,
                ];
            } else {
                return [
                    'message' => 'Invalid credentials, please try again',
                    'access' => 'Rejected',
                    'status' => JsonResponse::HTTP_UNAUTHORIZED,
                ];
            }
        } catch (Exception $e) {
            return [
                'message' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Logs out the user and deletes the access token
     *
     * @param Request $request
     * @return array
     * @throws AuthenticationException
     */
    public function logout($request): array
    {
        if (Auth::check()) {
            // Employee::where('id', Auth::user()->employee->id)->update(['loggedIn' => false]);
            $request->user()->tokens()->where('name', $request->user()->email)->delete();
            return  [
                "message" => "Logout successful",
                "access" => "Revoked",
                "status" => JsonResponse::HTTP_OK,
            ];
        } else {
            return new AuthenticationException();
        }
    }

    /**
     * Authenticate a user and exchange token
     *
     * @param array $credentials
     * @return array
     * @throws AuthenticationException
     */
    public function tokenExchange($request): array
    {
        if (Auth::check()) {
            $user = $request->user();
            return  [
                "message" => "Token exchange successful",
                'user' => new LoginResource($user),
                "status" => JsonResponse::HTTP_OK,
            ];
        } else {

            return new AuthenticationException();
        }
    }

    /**
     * Authenticate a user and exchange token for notification.
     *
     * @return array
     * @throws AuthenticationException
     */
    public function tokenExchangeForNotification(): array
    {
        $notifications =  $this->notificationRepository->fetchByEmployeeId();
        return [
            'notifications' => FetchNotificationResource::collection($notifications),
            'status' => JsonResponse::HTTP_OK,
        ];
    }

    /**
     * Authenticate a user with the provided credentials tru otp
     *
     * @param array $credentials
     * @return array
     * @throws ModelNotFoundException
     */
    public function authUserOnPasswordChange($request): array
    {
        try {

            $cleanData = $request->validated();
            $user = auth()->user();

            if (Hash::check($cleanData['password'], $user->password)) {
                return [
                    'message' => 'Old password can not be used as new password, Please choose different password.',
                    'access' => 'Rejected',
                    'status' => JsonResponse::HTTP_NOT_ACCEPTABLE
                ];
            }

            if ($this->authRepository->updatePassword($user->email, $cleanData['password'])) {
                $request->user()->tokens()->delete();
                return [
                    'message' => 'Password updated successfully',
                    'user' => new LoginResource($user),
                    'status' => JsonResponse::HTTP_ACCEPTED
                ];
            }

            return [
                'message' => 'Failed to update password, please try again',
                'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            ];
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'No user found, please try again with valid email and password',
                'access' => 'Rejected',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Invalid request',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }

    /**
     * Save new password on account set up
     *
     * @param Request $request
     * @return array
     * @throws ModelNotFoundException
     */
    public function savePasswordOnAcccountSetUp($request): array
    {
        try {

            $cleanData = $request->validated();

            $user = $this->authRepository->findByEmail($request->user()->email);

            if ($this->authRepository->updatePassword($request->user()->email, $cleanData['password'])) {

                return [
                    'message' => 'Account password setup successfully',
                    'user' => new LoginResource($user),
                    'status' => JsonResponse::HTTP_ACCEPTED,
                ];
            } else {

                return [
                    'message' => 'Failed to save new password, please try again',
                    'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                ];
            }
        } catch (ModelNotFoundException $e) {

            return [
                'message' => 'No user found, please try again with valid email',
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (Exception $e) {


            Log::info([$e, $request->user()->email, $cleanData['password']]);

            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,

            ];
        }
    }

    /**
     * change admin initial password
     * Authenticate a user with the provided credentials.
     *
     * @param array $credentials
     * @return array
     * @throws ModelNotFoundException
     */
    public function AuthUserOnInitialPasswordChange($request): array
    {

        try {

            $cleanData = $request->validated();


            $user = $this->authRepository->findByEmail(auth()->user()->email);

            if (!Hash::check($cleanData['old_password'], $user['password'])) {

                return [
                    'message' => 'Old password does not match, Please use the correct old password.',
                    'status' => JsonResponse::HTTP_NOT_ACCEPTABLE,
                ];
            }

            if ($cleanData['old_password'] === $cleanData['password']) {

                return [
                    'message' => 'Old password can not be used as new password, Please use different password.',
                    'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                ];
            }

            if ($this->authRepository->updatePassword(auth()->user()->email, $cleanData['password'])) {

                return [
                    'message' => 'Password updated successfully',
                    'status' => JsonResponse::HTTP_ACCEPTED,
                ];
            } else {

                return [
                    'message' => 'Failed to update password, please try again',
                    'status' => JsonResponse::HTTP_EXPECTATION_FAILED,
                ];
            }
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,

            ];
        }
    }


    /**
     * change profile seetings password
     *
     * @param array $credentials
     * @return array
     * @throws ModelNotFoundException
     */
    public function profileUpdatePassword($request): array
    {

        try {

            $cleanData = $request->validated();

            $user = $this->authRepository->findByEmail(auth()->user()->email);

            if (!Hash::check($cleanData['current_password'], $user['password'])) {

                return [
                    'message' => 'Current password does not match, Please use the current password.',
                    'status' => JsonResponse::HTTP_NOT_ACCEPTABLE,
                ];
            }

            if ($cleanData['current_password'] === $cleanData['password']) {

                return [
                    'message' => 'Current password can not be used as new password, Please use different password.',
                    'status' => JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                ];
            }
            if ($this->authRepository->updatePassword(auth()->user()->email, $cleanData['password'])) {

                return [
                    'message' => 'Password updated successfully',
                    'status' => JsonResponse::HTTP_OK,
                ];
            } else {

                return [
                    'message' => 'Failed to update password, please try again',
                    'status' => JsonResponse::HTTP_EXPECTATION_FAILED,
                ];
            }
        } catch (Exception $e) {

            return [
                'error' => $e->getMessage(),
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,

            ];
        }
    }
}