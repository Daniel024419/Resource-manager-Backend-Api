<?php

namespace Tests\Unit\API\V1\repository\Department;

use App\Models\V1\Department\Department;
use App\Models\V1\Employee\Employee;
use App\Repository\V1\Department\DepartmentRepository;
use Database\Factories\V1\Department\DepartmentFactory;
use Database\Factories\V1\Employee\EmployeeFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentRepositoryUnitTest extends TestCase
{
    use RefreshDatabase;

    // Instance of DepartmentRepository to be used in tests
    private $departmentRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a new instance of DepartmentRepository for each test
        $this->departmentRepository = new DepartmentRepository;
    }

    // Test fetching all departments
    public function testFetchAllDepartment()
    {
        // Create two departments using the factory
        $dep1 = $this->createDepartment();
        $dep2 = $this->createDepartment();

        // Fetch all departments
        $allDeps = $this->departmentRepository->fetch();

        // Assert that both department names are present in the result
        $this->assertContains($dep1->name, $allDeps->pluck('name'));
        $this->assertContains($dep2->name, $allDeps->pluck('name'));
    }

    // Test saving a new department
    public function testsave()
    {
        // Generate data for a new department using the factory
        $newDepartmentData = DepartmentFactory::new()->make()->toArray();

        // Call the method to save the new department
        $savedDepartment = $this->departmentRepository->save($newDepartmentData);

        // Check if the department was saved successfully
        $this->assertInstanceOf(Department::class, $savedDepartment);

        // Check if the department is present in the database
        $this->assertDatabaseHas('departments', [
            'id' => $savedDepartment->id,
            'name' => $savedDepartment->name
        ]);
    }

    // Test updating a department by ID
    public function testupdateById()
    {
        // Create a department using the factory
        $department = $this->createDepartment();

        // Generate updated data for the department
        $updatedDepartmentData = [
            'id' => $department->id,
            'name' => 'depsNameChanged',
        ];

        // Call the method to update the department
        $updatedDepartment = $this->departmentRepository->updateById($updatedDepartmentData);

        // Reload all departments from the database
        $reloadAllDepartment = $this->departmentRepository->fetch();

        // Check if the department was updated successfully
        $this->assertTrue($updatedDepartment > 0);

        // Check if the department in the database has the updated data
        $this->assertContains($updatedDepartmentData['name'], $reloadAllDepartment->pluck('name'));
    }

    // Test deleting a department by ID
    public function testdeleteById()
    {
        // Create a department using the factory
        $department = $this->createDepartment();

        // Call the method to delete the department by ID
        $deleteDepartment = $this->departmentRepository->deleteById($department->id);

        // Check if the department was deleted successfully
        $this->assertTrue($deleteDepartment);
    }

    // Test storing employee department by name
    public function teststoreByName()
    {
        // Create a department using the factory
        $department = $this->createDepartment();

        // Create an employee using the factory
        $employee = Employee::factory()->create();

        // Call the method to store the employee department by name
        $assignDepartmentToUser = $this->departmentRepository->storeByName($department->name, $employee->id);

        // Check if the employee department was stored successfully
        $this->assertTrue($assignDepartmentToUser);
    }

    // Test updating employee department by name
    public function testupdateByName()
    {
        // Create two departments using the factory
        $department1 = $this->createDepartment();
        $department2 = $this->createDepartment();

        // Create an employee using the factory
        $employee = Employee::factory()->create();

        // Store the employee in the first department
        $this->departmentRepository->storeByName($department1->name, $employee->id);

        // Call the method to update the employee department to the second department by name
        $updateProfileDepartment = $this->departmentRepository->updateByName($department2->name, $employee->id);

        // Check if the employee department was updated successfully
        $this->assertTrue($updateProfileDepartment);
    }

    // Helper function to create a department
    private function createDepartment(): Department
    {
        return Department::factory()->create();
    }
}
