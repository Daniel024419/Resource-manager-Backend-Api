<?php

namespace Tests\Unit\API\V1\services\users;

use Mockery;
use Tests\TestCase;
use App\Enums\Roles;
use Illuminate\Support\Arr;
use App\Models\V1\User\User;
use App\Models\V1\Employee\Employee;
use App\Service\V1\Users\UserService;
use App\Models\V1\Department\Department;
use App\Repository\V1\Skills\SkillsRepository;
use App\Models\V1\Specialization\Specialization;
use App\Repository\V1\Employee\EmployeeRepository;
use App\Repository\V1\Users\UserInterfaceRepository;
use App\Service\V1\Notification\NotificationService;
use App\Repository\V1\Department\DepartmentRepository;
use App\Repository\V1\Notification\NotificationRepository;
use App\Repository\V1\Specialization\SpecializationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class userServicesUnitTest extends TestCase
{

    use RefreshDatabase;

    protected $userService;
    protected $userRepositoryMock;
    protected $employeeRepositoryMock;
    protected $departmentRepositoryMock;
    protected $specializationRepositoryMock;
    protected $skillsRepositoryMock;
    protected $notificationRepositoryMock;
    protected $notificationServiceMock;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for dependencies
        $this->userRepositoryMock = Mockery::mock(UserInterfaceRepository::class);
        $this->employeeRepositoryMock = Mockery::mock(EmployeeRepository::class);
        $this->departmentRepositoryMock = Mockery::mock(DepartmentRepository::class);
        $this->specializationRepositoryMock = Mockery::mock(SpecializationRepository::class);
        $this->skillsRepositoryMock = Mockery::mock(SkillsRepository::class);
        $this->notificationRepositoryMock = Mockery::mock(NotificationRepository::class);
        $this->notificationServiceMock = Mockery::mock(NotificationService::class);

        // Create an instance of the UserService with mocked dependencies
        $this->userService = new UserService(
            $this->userRepositoryMock,
            $this->employeeRepositoryMock,
            $this->notificationServiceMock,
            $this->specializationRepositoryMock,
            $this->departmentRepositoryMock,
            $this->notificationRepositoryMock,
            $this->skillsRepositoryMock
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testsave()
    {
        $user = $this->user = $this->createUser();
        $deparment = Department::factory()->create();
        $specialization = Specialization::factory()->create();


        $roles = [Roles::ADMIN->value, Roles::MGT->value];

        Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue(Arr::random($roles)),
        ]);

        $response = $this->post('/api/v1/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $response['accessToken'],
            'Accept' => 'application/json',
        ];

        $this->assertEquals(200, $response->getStatusCode());

        $fakeMail = User::factory()->make()->toArray();

        $requestData = [
            'email' => $fakeMail['email'],
            'roles' => Roles::BU->value,
            'skills' => 'PHP',
            'department' => strtoupper($deparment->name),
            'specialization' => strtoupper($specialization->name),
        ];
        $saveResponse = $this->withHeaders($headers)->post(
            '/api/v1/users/store',
            $requestData
        );
        $this->assertEquals(200, $saveResponse->status());
    }

    private function createUser(): User
    {
        return User::factory()->create();
    }
}