<?php

namespace Tests\Unit\API\V1\repository\employees;

use Tests\TestCase;
use App\Models\V1\User\User;
use App\Models\V1\Employee\Employee;
use App\Repository\V1\Employee\EmployeeRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeRepositoryUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $employeeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeRepository = new EmployeeRepository();
    }

    // Test fetchBookable
    public function testfetchBookable()
    {
        $result = $this->employeeRepository->fetchBookable(1);
        $this->assertNotNull($result);
    }

    // Test save
    public function testsave()
    {
        $employeeData = User::factory()->make()->toArray();

        $result = $this->employeeRepository->save($employeeData);

        // Corrected assertion
        $this->assertNotNull($result);
    }


    // Test findByemail
    public function testfindByemail()
    {
        $user = User::factory()->create();

        $result = $this->employeeRepository->findByemail($user->email);

        $this->assertNotNull($result);
    }

    // Test findByParam
    public function testfindByParam()
    {
        $employee = Employee::factory()->create();

        $result = $this->employeeRepository->findByParam($employee->userId);

        $this->assertNotNull($result);
    }

    // Test findByRefId
    public function testfindByRefId()
    {
        $employee =  Employee::factory()->create();

        $result = $this->employeeRepository->findByRefId($employee->userId);

        $this->assertNotNull($result);
    }

    // Test findByAuthId
    public function testfindByAuthId()
    {
        // Prepare test data
        $employee =  Employee::factory()->create();

        $result = $this->employeeRepository->findByAuthId($employee->userId);

        $this->assertNotNull($result);
    }

    // Test updateByRefId
    public function testupdateByRefId()
    {
        $employee =  Employee::factory()->create();

        // Prepare test data for update
        $employeeData = [
            'userId' => $employee->userId,
            'firstName' => 'New',
            'lastName' => 'Name',
        ];


        $result = $this->employeeRepository->updateByRefId($employeeData);

        $this->assertEquals($employeeData['firstName'], $result->firstName);

        $this->assertEquals($employeeData['lastName'], $result->lastName);
    }
}