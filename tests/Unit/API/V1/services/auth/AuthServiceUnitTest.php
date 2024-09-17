<?php

namespace Tests\Unit\API\V1\services\auth;

use Mockery;
use Carbon\Carbon;
use Tests\TestCase;
use App\Enums\Roles;
use App\Models\V1\Otp\Otp;
use Illuminate\Support\Arr;
use App\Models\V1\User\User;
use Illuminate\Http\JsonResponse;
use App\Models\V1\Employee\Employee;
use App\Service\V1\Auth\AuthService;
use App\Service\V1\Users\UserService;
use App\Service\V1\Employee\EmployeeService;
use App\Repository\V1\Auth\AuthInterfaceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repository\V1\Notification\NotificationRepository;

class AuthServiceUnitTest extends TestCase
{
    use RefreshDatabase;
    private User $user;
    private $roles;
    private $authRepository, $authService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock dependencies if needed
        $this->authRepository = $this->createMock(AuthInterfaceRepository::class);
        $userService = $this->createMock(UserService::class);
        $employeeService = $this->createMock(EmployeeService::class);
        $notificationRepository = $this->createMock(NotificationRepository::class);

        $this->authService = new AuthService($this->authRepository, $userService, $employeeService, $notificationRepository);

        $this->roles = [Roles::ADMIN->value, Roles::MGT->value];
    }

    public function testAuthServicefindByemail()
    {

        $user = $this->user = $this->createUser();

        // Define a sample response from the repository
        $expectedResponse = ['user' => $user];

        // Set up expectations for the mock
        $this->authRepository->expects($this->once())
            ->method('findByEmail')
            ->with($this->equalTo($user->email))
            ->willReturn($expectedResponse);

        // Call the method and assert the result
        $result = $this->authService->findByEmail($user->email);

        $this->assertEquals($expectedResponse, $result);
    }

    public function testAuthServiceauthenticateUser()
    {
        $user = $this->user = $this->createUser();
        Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        $response = $this->post('/api/v1/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200);
    }


    public function testAuthServiceLogoutService()
    {
        $user = $this->user = $this->createUser();
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

        $this->withHeaders($headers)->post('/api/v1/users/logout');

        $response->assertStatus(200);
    }

    public function testLogoutUnauthenticated()
    {

        $headers = [
            'Authorization' => 'Bearer ' . '',
            'Accept' => 'application/json',
        ];

        $response =  $this->withHeaders($headers)->post('/api/v1/users/logout');

        $response->assertStatus(401);
    }

    public function testAuthServiceTokenExchangeService()
    {
        $user = $this->user = $this->createUser();
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

        $result = $this->withHeaders($headers)->get('/api/v1/users/token/exchange');


        $result->assertStatus(JsonResponse::HTTP_OK);
    }

    public function testTokenExchangeForNotification()
    {
        $user = $this->user = $this->createUser();
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

        $result = $this->withHeaders($headers)->get('/api/v1/users/notifications/fetch');

        // Assert that the response status is OK (200)
        $result->assertStatus(JsonResponse::HTTP_OK);

        // Assert that the response contains a JSON structure with a 'notifications' key
        $result->assertJsonStructure(['notifications']);

        // Decode the response JSON content
        $data = json_decode($result->getContent(), true);

        // Assert that the 'notifications' key is an array (collection)
        $this->assertIsArray($data['notifications']);
    }


    public function testAuthServiceAuthUserOnPasswordChange()
    {

        $user = $this->user = $this->createUser();
        $fakePassword = User::factory()->make();
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
        $now = Carbon::now();
        $expiryTime = $now->addSeconds(600);

        // Format the time in a 12-hour format with AM/PM
        $expiryTimeFormatted = $expiryTime->format('Y-m-d h:i:s A');


        $otp = ['user_id' => $user->id, 'otp' => 123456, 'expires_at' => $expiryTimeFormatted];

        Otp::create($otp);

        $data =  [
            'email' =>  $user->email,
            'password_confirmation' => $fakePassword->password,
            'password' => $fakePassword->password,
            'otp' => $otp['otp'],
        ];


        $result = $this->withHeaders($headers)->put(
            '/api/v1/users/update/password',
            $data
        );

        // Assert that the response status is OK (200)
        $result->assertStatus(JsonResponse::HTTP_ACCEPTED);
    }

    public function testAuthServicesavePasswordOnAcccountSetUp()
    {

        $user = $this->user = $this->createUser();
        $fakePassword = User::factory()->make();
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


        $data =  [
            'email' =>  $user->email,
            'password_confirmation' => $fakePassword->password,
            'password' => $fakePassword->password,
        ];


        $result = $this->withHeaders($headers)->post(
            '/api/v1/users/set/new/password',
            $data
        );

        // Assert that the response status is OK (200)
        $result->assertStatus(JsonResponse::HTTP_ACCEPTED);
    }

    public function testAuthServiceAuthUserOnInitialPasswordChange()
    {
        $user = $this->user = $this->createUser();
        $fakePassword = User::factory()->make();
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


        $data =  [
            'email' =>  $user->email,
            'old_password' => 'password',
            'password' => $fakePassword->password,
        ];


        $result = $this->withHeaders($headers)->put(
            '/api/v1/users/update/initial/password',
            $data
        );

        // Assert that the response status is OK (200)
        $result->assertStatus(JsonResponse::HTTP_ACCEPTED);
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}