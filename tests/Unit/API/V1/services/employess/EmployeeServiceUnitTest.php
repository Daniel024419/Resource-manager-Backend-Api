<?php

namespace Tests\Unit\API\V1\repository\employess;

use Tests\TestCase;
use App\Enums\Roles;
use Illuminate\Support\Arr;
use App\Models\V1\User\User;
use Illuminate\Http\JsonResponse;
use App\Models\V1\Employee\Employee;
use App\Repository\V1\Users\UserRepository;
use App\Service\V1\Employee\EmployeeService;
use App\Repository\V1\Skills\SkillsRepository;
use App\Repository\V1\Employee\EmployeeRepository;
use App\Repository\V1\Department\DepartmentRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Specialization\SpecializationRepository;
use Illuminate\Support\Facades\Log;

class EmployeeServiceUnitTest extends TestCase
{

    use RefreshDatabase;

    protected $validTestUserData = array();
    protected $employeeData = array();
    protected $employeeRepository;
    private $roles;
    protected $employeeService;

    protected function setUp(): void
    {
        parent::setUp();

        $id = rand(1, 100);

        // Valid user data for testing
        $this->validTestUserData = [
            'id' => $id,
            'email' => 'jane@gmail.com',
            'password' => "Salasie",
        ];

        // Example employee data
        $this->employeeData = [
            'userId' => $this->validTestUserData['id'], // Use the ID of the created user
            'refId' => md5(rand()),
            'roles' => 'Admin',
            // Add other required fields
        ];

        // Mocking the EmployeeRepository (replace this with a mock if needed)
        $this->employeeRepository = $this->getMockBuilder(EmployeeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Creating an instance of EmployeeService with necessary dependencies
        $this->employeeService = new EmployeeService(
            new EmployeeRepository(),
            new UserRepository(),
            new SpecializationRepository(),
            new DepartmentRepository(),
            new SkillsRepository()
        );


        $this->roles = [Roles::ADMIN->value, Roles::MGT->value];
    }


    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testEmployeeServiceUnitTestfindByemail()
    {

        $user = User::factory()->create();

        // Call the findByemail method
        $search = $this->employeeService->findByemail($user->email);

        // Assert that the employee is either null or an instance of Employee
        $this->assertEquals($search['email'], $user->email);
    }


    public function testEmployeeServiceUnitTestfindByParam()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue(Arr::random($this->roles)),
        ]);

        $result = $this->employeeService->findByParam($employee->userId);

        // Assert that the returned result matches the created employee
        $this->assertEquals($employee->userId, $result['userId']);
    }


    public function testEmployeeServiceUnitTestfindByRefId()
    {

        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue(Arr::random($this->roles)),
        ]);

        $employee = $this->employeeService->findByRefId($employee->userId);

        $this->assertTrue($employee === null || $employee instanceof Employee);
    }

    public function testEmployeeServiceUnitTestupdateProfile()
    {
        $employee = Employee::factory()->create([
            'roleId' => Roles::getRoleIdByValue(Arr::random($this->roles)),
        ]);

        $email = $employee['authInfo']['email'];

        $response = $this->post('/api/v1/users/login', [
            'email' => $email,
            'password' => 'password',
        ]);


        $accessToken = $response->json('accessToken');

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ];

        $employeeData = [
            'userId' => $employee->userId,
            'firstName' => 'John',
            'lastName' => 'kol',
            'phoneNumber' => '0987654321',
        ];

        $result = $this->withHeaders($headers)
            ->put('/api/v1/users/settings/update/profile', $employeeData);

        $result->assertStatus(200);


        $search = $this->employeeService->findByemail($email);

        // Log::info($employeeData);
        // Log::info($result);
        $this->assertEquals($search['employee']['firstName'], $employeeData['firstName']);
    }
}
