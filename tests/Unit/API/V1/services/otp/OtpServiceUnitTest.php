<?php
//OtpServiceUnitTest
namespace Tests\Unit\API\V1\services\otp;

use Tests\TestCase;
use App\Models\V1\User\User;
use Illuminate\Http\Request;
use App\Service\V1\Otp\OtpService;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Employee\Employee;
use App\Service\V1\Auth\AuthService;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Notification;
use App\Repository\V1\Otp\OTPRepositoryinterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Service\V1\Notification\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Notifications\V1\Auth\AccountResetOTPNotification;

class OtpServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    private $otpRepository;
    private $notificationService;
    private $userRepository;
    private $authService;
    private $otpService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock dependencies if needed

        $this->otpRepository = $this->createMock(OTPRepositoryinterface::class);
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->userRepository = $this->createMock(UserInterfaceRepository::class);
        $this->authService = $this->createMock(AuthService::class);

        $this->otpService = new OtpService(
            $this->otpRepository,
            $this->notificationService,
            $this->userRepository,
            $this->authService
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testUnitsave()
    {
        $otpData = [
            'user_id' => 1,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ];

        $this->otpRepository->expects($this->once())
            ->method('save')
            ->with($otpData)
            ->willReturn((object) $otpData);

        $result = $this->otpService->save($otpData);

        $this->assertEquals((object) $otpData, $result);
    }

    public function testUnitsendOTP()
    {
        // Create User and Employee
        $user = User::create([
            'id' => 1,
            'email' => 'jane@gmail.com',
            'password' => 'invalidpassword',
        ]);

        Employee::create([
            'userId' => $user['id'],
            'refId' => md5(rand()),
            'roles' => "Administrator",
            'firstName' => 'jane',
        ]);

        // Mock dependencies
        Notification::fake();
        $this->userRepository->expects($this->once())
            ->method('findByemail')
            ->with('jane@gmail.com')
            ->willReturn($user);

        // Add debugging statement
        //Log::info('Mocked User:', ['user' => $user]);

        // Mock notificationService
        // Assuming $user is an instance of App\Models\V1\User\User
        $this->notificationService->expects($this->once())
            ->method('sendOTP')
            ->with(
                $this->identicalTo($user),
                $this->isInstanceOf(AccountResetOTPNotification::class)
            )
            ->willReturn(true);

        // Execute the method
        $result = $this->otpService->sendOTP('jane@gmail.com');

        // Add debugging statement
        //Log::info('Test Result:', ['result' => $result]);

        // Assert specific expectations
        $this->assertEquals(200, $result['status']);
        $this->assertEquals('OTP mail sent successfully, Please Check your mail', $result['message']);
    }



    public function testUnitdeleteByUserId()
    {
        $user = User::create([
            'id' => 1,
            'email' => 'jane@gmail.com',
            'password' => 'invalidpassword',
        ]);

        // Mock OtpRepository
        $this->otpService->OtpRepository
            ->expects($this->once())
            ->method('deleteByUserId')
            ->with($user->id)  // Use the actual user ID here
            ->willReturn(1);

        $result = $this->otpService->deleteByUserId($user->id);

        $this->assertEquals(1, $result);
    }
}