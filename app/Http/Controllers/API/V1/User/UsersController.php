<?php

namespace App\Http\Controllers\API\V1\User;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\EmailRequest;
use App\Http\Requests\Auth\sendOTPRequest;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Response\Store\StoreResponse;
use App\Service\V1\Otp\OtpInterfaceService;
use App\Http\Requests\Auth\VerifyOTPRequest;
use App\Http\Response\Delete\DeleteResponse;
use App\Http\Response\Update\UpdateResponse;
use App\Service\V1\Employee\EmployeeService;
use App\Http\Requests\User\UserDeleteRequest;
use App\Http\Requests\User\UserSearchRequest;
use App\Service\V1\Auth\AuthInterfaceService;
use App\Service\V1\Users\UserInterfaceService;
use App\Http\Requests\Auth\savePasswordRequest;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Requests\Auth\updatePasswordRequest;
use App\Http\Requests\User\UpdateUserInfoRequest;
use App\Http\Requests\Employee\UpdateProfileRequest;
use App\Service\V1\Employee\EmployeeInterfaceService;
use App\Service\V1\Users\UserArchiveInterfaceService;
use App\Http\Requests\Employee\EditUserProfileRequest;
use App\Http\Requests\Auth\updateInitialPasswordRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Auth\updateSettingsPasswordRequest;
use App\Http\Requests\Employee\AdminEditUserProfileRequest;

class UsersController extends Controller
{
    protected $userService,
        $employeeService, $deleteResponseHandler,
        $fecthResponseHandler, $authService,
        $updateResponseHandler, $storeResponseHandler,
        $userArchiveService, $otpService;

    public function __construct(
        FetchResponse $fecthResponseHandler,
        DeleteResponse $deleteResponseHandler,
        UserInterfaceService $userService,
        UpdateResponse $updateResponseHandler,
        AuthInterfaceService $authService,
        EmployeeInterfaceService $employeeService,
        StoreResponse $storeResponseHandler,
        OtpInterfaceService  $otpService,

    ) {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->fecthResponseHandler = $fecthResponseHandler;
        $this->deleteResponseHandler = $deleteResponseHandler;
        $this->updateResponseHandler = $updateResponseHandler;
        $this->employeeService = $employeeService;
        $this->storeResponseHandler = $storeResponseHandler;
        $this->otpService = $otpService;
    }

    /** get all managers
     * @var $request
     * @return Collection
     */
    public function fetchAllManagers()
    {
        try {
            $fetchResponse = $this->userService->fetchAllManagers();
            return $this->fecthResponseHandler->handleFetchResponse($fetchResponse);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /** get all employees
     * @var $request
     * @return Collection
     */
    public function fetch(Request $request)
    {
        try {
            $query = $request->query('query');
            $fetchResponse = $this->userService->fetchAllUsers($query);
            return $this->fecthResponseHandler->handleFetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * fetch Bookable users
     * @var $request
     * @return Collection
     */
    public function fetchBookable(Request $request)
    {
        try {
            $query = $request->query('query');
            $fetchResponse = $this->employeeService->fetchBookable($query);
            return $this->fecthResponseHandler->handleFetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * register user info from frontend
     * @param RegisterUserRequest $request
     * @var $request
     */
    public function store(RegisterUserRequest $request)
    {

        try {
            // Save the new user using the service and return the user information
            $storeResponseUser = $this->userService->save($request);
            return $this->storeResponseHandler->handleStoreResponse($storeResponseUser);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *get all active Account
     * @var $request
     * @return Collection
     */
    public function active(Request $request)
    {
        try {
            //$query = $request->query('query');
            $fetchResponse = $this->userService->active();
            return $this->fecthResponseHandler->handleFetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *get all inactive Account
     * @var $request
     * @return Collection
     */
    public function inactive(Request $request)
    {
        try {
            //$query = $request->query('query');
            $fetchResponse = $this->userService->inactive();
            return $this->fecthResponseHandler->handleFetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     *
     *@param UserSearchRequest $request
     * @var $request
     * @return Collection
     */
    public function search(UserSearchRequest $request)
    {
        try {
            // Retrieve validated input directly from the request
            $cleanData = $request->validated();
            $fetchResponse = $this->userService->search($cleanData['search_param']);
            return $this->fecthResponseHandler->handleFetchResponse($fetchResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * delete the user's information.
     *
     * @param UserDeleteRequest $request
     * @return JsonResponse
     */
    public function delete(UserDeleteRequest $request): JsonResponse
    {
        try {
            $deleteResponse =  $this->userService->deleteUser($request);
            // Use the handleUserDeleteResponse to handle the response
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the user's profile password on settings.
     *
     * @param savePasswordRequest $request
     * @return JsonResponse
     */
    public function profileUpdatePassword(updateSettingsPasswordRequest $request): JsonResponse
    {
        try {
            $updatedResponse = $this->authService->profileUpdatePassword($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * upadte the user's information.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            // Update the user using the service
            $updatedResponse = $this->employeeService->updateProfile($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * edit the user's information by admin or manager.
     *
     * @param EditUserProfileRequest $request
     * @return JsonResponse
     */
    public function edit(EditUserProfileRequest $request): JsonResponse
    {
        try {
            // edit the user using the service
            $updatedResponse = $this->employeeService->editProfile($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * edit admin user's information.
     *
     * @param UpdateProfileRequest $request
     * @return JsonResponse
     */
    public function adminUpdateProfile(AdminEditUserProfileRequest $request): JsonResponse
    {
        try {
            // Update the user using the service
            $updatedResponse = $this->employeeService->adminUpdateProfile($request);
            return $this->updateResponseHandler->handleUpdateResponse($updatedResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Setup the user's information.
     *
     * @param UpdateUserInfoRequest $request
     * @return JsonResponse
     */
    public function accountSetup(UpdateUserInfoRequest $request): JsonResponse
    {
        try {
            // Update the user using the service
            $updateResponse = $this->employeeService->accountSetup($request);
            return $this->updateResponseHandler->handleUpdateResponse($updateResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Update the user's new password on account setup.
     *
     * @param savePasswordRequest $request
     * @return JsonResponse
     */
    public function NewPassword(savePasswordRequest $request): JsonResponse
    {
        try {

            $updateResponse = $this->authService->savePasswordOnAcccountSetUp($request);
            return $this->updateResponseHandler->handleUpdateResponse($updateResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * update admin initial password
     * @param updateInitialPasswordRequest $request
     * @var $request
     */

    public function updateInitialPassword(updateInitialPasswordRequest $request): JsonResponse
    {
        try {

            $updateResponse = $this->authService->AuthUserOnInitialPasswordChange($request);
            return $this->updateResponseHandler->handleUpdateResponse($updateResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send OTP code to the user's registered email address.
     *
     * @param sendOTPRequest $request
     * @return JsonResponse
     */
    public function sendOTPcode(sendOTPRequest $request): JsonResponse
    {
        try {
            // Retrieve validated input directly from the request
            $cleanData = $request->validated();
            $otpResponse = $this->otpService->sendOTP($cleanData['email']);
            return $this->updateResponseHandler->handleUpdateResponse($otpResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send OTP code to the user's registered email address.
     *
     * @param VerifyOTPRequest $request
     * @return JsonResponse
     */
    public function verifyOTPcode(VerifyOTPRequest $request): JsonResponse
    {
        try {
            $verifyResponse =  $this->otpService->verifyCode($request);
            return $this->updateResponseHandler->handleUpdateResponse($verifyResponse);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * update users password
     *
     * @param updatePasswordRequest $request
     * @var $request
     */
    function updatePassword(updatePasswordRequest $request): JsonResponse
    {
        try {
            $updateResponse =  $this->authService->authUserOnPasswordChange($request);
            return $this->updateResponseHandler->handleUpdateResponse($updateResponse);
        } catch (Exception $e) {

            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    /**
     * send account invite again to users
     * @param EmailRequest $request
     * @var $request
     */
    public function reInvite(EmailRequest $request)
    {
        try {
            // send account setup invite again
            $sendResponse = $this->userService->reInviteUsers($request);
            return $this->storeResponseHandler->handleStoreResponse($sendResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
  /**
     * fetch all aecived users
     * @method get
     * @param
     */
    public function archivesFetch(): JsonResponse
    {
        try {
            $fetchResponse =  $this->userService->archivesFetch();
            // Use the AuthResponseHandler to handle the response
            return $this->fecthResponseHandler->handlefetchResponse($fetchResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * unarchive User
     * @param EmailRequest $request
     * @var $request
     */
    public function archivesRestore(EmailRequest $request)
    {
        try {
            $deleteResponse = $this->userService->archivesRestore($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * delete archived Users
     * @param EmailRequest $request
     * @var $request
     */
    public function archivesDelete(EmailRequest $request): JsonResponse
    {
        try {
            $deleteResponse = $this->userService->archivesDelete($request);
            return $this->deleteResponseHandler->handleDeleteResponse($deleteResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * search archived Users
     * @param UserSearchRequest $request
     * @var $request , $UserId
     * @return JsonResponse
     */
    public function archivesSearch(UserSearchRequest $request): JsonResponse
    {
        try {
            $searchResponse = $this->userService->archivesSearch($request);
            return $this->fecthResponseHandler->handlefetchResponse($searchResponse);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}