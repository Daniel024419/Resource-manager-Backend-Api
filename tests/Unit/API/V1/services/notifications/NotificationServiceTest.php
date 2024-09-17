<?php

namespace Tests\Unit\API\V1\services\notifications;

use Mockery;
use Tests\TestCase;
use App\Enums\Roles;
use Illuminate\Support\Arr;
use App\Models\V1\User\User;
use Illuminate\Http\JsonResponse;
use App\Models\V1\Employee\Employee;
use Illuminate\Support\Facades\Notification;
use App\Repository\V1\Employee\EmployeeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Service\V1\Notification\NotificationService;
use App\Notifications\V1\Auth\AccountResetOTPNotification;
use App\Repository\V1\Notification\NotificationRepository;
use App\Notifications\V1\Auth\AccountCompletionNotification;
use App\Models\V1\Notification\Notification as NotificationModel;
use App\Notifications\V1\Projects\NewProjectReminderNotification;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $notificationRepository;
    protected $employeeRepository;
    protected $notificationService;
    private $roles;


    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationRepository = new NotificationRepository();

        $this->roles = [Roles::ADMIN->value, Roles::MGT->value];

        $this->notificationRepository = $this->createMock(NotificationRepository::class);
        $this->employeeRepository = $this->createMock(EmployeeRepository::class);
        $this->notificationService = new NotificationService($this->notificationRepository, $this->employeeRepository);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testSendAccountCompletionNotification()
    {

        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);


        $notification = new AccountCompletionNotification(

            "AccountCompletion",
            $user->id,
            $user->email,
            $employee->roleId,
            "QQW2qqqeqeqeqqeqqqqqqq",
            $user->email,
            $user->email
        );

        Notification::fake();

        $result = $this->notificationService->accountCompletion($user, $notification);

        Notification::assertSentTo($user, AccountCompletionNotification::class);
        $this->assertTrue($result);
    }
    public function testSendOTPNotification()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        Notification::fake();

        $notification = new AccountResetOTPNotification('Test otp notificstion', $employee['firstName'], rand(111111, 99999));

        $result = $this->notificationService->accountCompletion($user, $notification);

        Notification::assertSentTo($user, AccountResetOTPNotification::class);
        $this->assertTrue($result);
    }

    public function testProjectAssignmentNotification()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        Notification::fake();

        $notification = new NewProjectReminderNotification(
            'test project notifications',
            $employee->firstName . ' ' . $employee->lastName,
            'test ',
            implode(', ', ['bob', 'John', 'lilo']),
            'examples details',
            Date('Y-m-d H:i:s'),
            Date('Y-m-d H:i:s'),
            $employee->firstName . ' ' . $employee->lastName,
            // $duration,
        );

        $result = $this->notificationService->projectAssignment($user, $notification);

        Notification::assertSentTo($user, NewProjectReminderNotification::class);
        $this->assertTrue($result);
    }
    /**
     * Test marking all notifications as read.
     *
     * @return void
     */
    public function testMarkAllAsRead()
    {
        $user = User::factory()->create();
        Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        $response = $this->post('/api/v1/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $response['accessToken'],
            'Accept' => 'application/json',
        ];
        $result = $this->withHeaders($headers)->post('/api/v1/users/notifications/mark/all/read', ['email' => $user->email]);

        $result->assertStatus(JsonResponse::HTTP_OK);
    }

    /**
     * Test marking one notification as read.
     *
     * @return void
     */
    public function testMarkOneAsRead()
    {
        $user = User::factory()->create();
        Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        $response = $this->post('/api/v1/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $response['accessToken'],
            'Accept' => 'application/json',
        ];


        $notification = NotificationModel::factory()->create();

        $result = $this->withHeaders($headers)->post('/api/v1/users/notifications/mark/one/read', ['notification_id' => $notification->id]);

        $result->assertStatus(JsonResponse::HTTP_OK);
    }
}