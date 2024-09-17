<?php

namespace App\Service\V1\Otp;


use Exception;
use Carbon\Carbon;
use RuntimeException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Auth\sendOTPResource;
use App\Repository\V1\Otp\OTPRepositoryinterface;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Service\V1\Notification\NotificationInterfaceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Notifications\V1\Auth\AccountResetOTPNotification;

class OtpService implements OtpInterfaceService
{
    public $OtpRepository, $notificationService, $userRepository, $authService;

    /**
     * OtpService Constructor.
     *
     * This constructor initializes the OtpService object.
     * It takes instances of the OTPRepositoryinterface, NotificationService, UserInterfaceRepository,
     * and AuthService classes as dependency injections.
     *
     * @param OTPRepositoryinterface $OtpRepository
     *     An instance of the OTPRepositoryinterface, providing data access methods for OTP-related operations.
     *     This instance will be injected into the OtpService.
     * @param NotificationInterfaceService $notificationService
     *     An instance of the NotificationService class, providing notification-related functionality.
     *     This instance will be injected into the OtpService.
     * @param UserInterfaceRepository $userRepository
     *     An instance of the UserInterfaceRepository, providing data access methods for user-related operations.
     *     This instance will be injected into the OtpService.
     */

    public function __construct(
        OTPRepositoryinterface $OtpRepository,
        NotificationInterfaceService $notificationService,
        UserInterfaceRepository $userRepository,
    ) {
        $this->OtpRepository = $OtpRepository;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
    }

    /**
     * Save a new OTP record
     *
     * @param array $OTPData
     * @return mixed
     */
    public function save(array $OTPData)
    {
        try {
            // pass the data to the repository
            $otp = $this->OtpRepository->save($OTPData);

            return $otp;
        } catch (ModelNotFoundException $e) {
            // return a ModelNotFoundException
            return new ModelNotFoundException();
        }
    }

    /**
     * verify otp code
     *
     * @param mixed $request
     * @return array response
     */
    public function verifyCode($request): array
    {
        try {
            $cleanData = $request->validated();
            $user =  $this->OtpRepository->findByEmail($cleanData['email']);

            if (empty($user)) {
                throw new ModelNotFoundException();
            }

            //pass the data for query
            $otpResponse = $this->OtpRepository->findByUserId($user['id']);

            if ( $otpResponse && $otpResponse['otp'] == $cleanData['otp'] && $otpResponse && strtotime($otpResponse['expires_at']) < time()) {
               
                return [
                    'message' => 'OTP has expired, Please try again',
                    'status' => JsonResponse::HTTP_NOT_ACCEPTABLE,
                ];

            } 

            // Check if OTP matches and has not expired
            if ($otpResponse && $otpResponse['otp'] == $cleanData['otp'] && strtotime($otpResponse['expires_at']) > time()) {

                $user->tokens()->delete();

                $token = $user->createToken('temporary-token', ['temporary-scopes'], now()->addMinutes(15))->plainTextToken;
                $this->deleteByUserId($user['id']);

                return [
                    'message' => 'OTP Verified',
                    'accessToken' => $token,
                    'status' => JsonResponse::HTTP_ACCEPTED
                ];

            } else {

                return [
                    'message' => 'Invalid OTP, Please try again',
                    'status' => JsonResponse::HTTP_NOT_ACCEPTABLE,
                ];
            }

        } catch (ModelNotFoundException $e) {

            return [
                "message" => "No opt found for this user,Please Try again",
                'access' => 'Rejected',
                'status' => JsonResponse::HTTP_NOT_FOUND
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Delete OTP by user id
     *
     * @param string $user_id
     * @return int
     */
    public function deleteByUserId(string $user_id): int
    {
        try {
            $response = $this->OtpRepository->deleteByUserId($user_id);
            return $response;
        } catch (ModelNotFoundException $e) {
            return new ModelNotFoundException();
        }
    }

    /**
     * send otp notification after user is found as valid user
     *
     * @param string $email
     * @return array
     */
    public function sendOTP(string $email): array
    {
        try {
            // Generate the OTP number
            $OTP = rand(111111, 99999);

            $user = $this->userRepository->findByEmail($email);
            if (empty($user)) {
                throw new ModelNotFoundException();
            }

            $user_id = $user['id'];
            $title = 'Account Recovery';

            // Delete old OTP if exists
            $this->OtpRepository->deleteByUserId($user_id);
            // Set the timezone
            date_default_timezone_set('Africa/Accra');

            // Set expiration time
            // Get the current date and time
            $now = Carbon::now();

            // Add 3000 seconds (10 minutes) to the current date and time
            $expiryTime = $now->addSeconds(600);

            // Format the time in a 12-hour format with AM/PM
            $expiryTimeFormatted = $expiryTime->format('Y-m-d h:i:s A');

            // Construct OTP data into an array
            $OTPData = ['user_id' => $user_id, 'otp' => $OTP, 'expires_at' => $expiryTimeFormatted];

            // Create a new OTP
            $otp = $this->OtpRepository->save($OTPData);
            if ($otp) {
                // Send OTP notification
                $notification = new AccountResetOTPNotification($title, $user['employee']['firstName'] ?? $user['email'], $OTP);
                $sendResponse = $this->notificationService->sendOTP($user, $notification);

                if (!$sendResponse) {
                    // Return success response
                    return [
                        'message' => 'Failed to send OTP notification, please try again',
                        'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                    ];
                }

                // Return success response
                return [
                    'message' => 'OTP mail sent successfully, Please Check your mail',
                    'user' => new sendOTPResource($user),
                    'status' => JsonResponse::HTTP_OK,
                ];
            }
        } catch (ModelNotFoundException $e) {
            return [
                "message" => "No user found, Please try again with a valid email",
                'status' => JsonResponse::HTTP_NOT_FOUND,
            ];
        } catch (RuntimeException $e) {
            return [
                'error' => $e->getMessage(),
                'message' => 'Unable to send reset notification, please try again',
                'status' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
            ];
        }
    }
}
