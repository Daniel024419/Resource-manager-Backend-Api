<?php

namespace Tests\Unit\API\V1\services\departments;

use Mockery;
use Tests\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Service\V1\Department\DepartmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repository\V1\Department\DepartmentInterfaceRepository;

class DepartmentServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    private $departmentRepository, $departmentService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock dependencies using Mockery
        $this->departmentRepository = Mockery::mock(DepartmentInterfaceRepository::class);
        $this->departmentService = new DepartmentService($this->departmentRepository);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testFetch()
    {
        $this->departmentRepository->shouldReceive('fetch')->once()->andReturn([]);

        $result = $this->departmentService->fetch();

        $this->assertEquals(JsonResponse::HTTP_OK, $result['status']);
    }

    public function testStore()
    {
        $request = $this->mockRequest(['name' => 'Test Department']);
        $this->departmentRepository->shouldReceive('save')->once()->andReturn(['id' => 1, 'name' => 'Test Department']);

        $result = $this->departmentService->store($request);

        $this->assertEquals(JsonResponse::HTTP_CREATED, $result['status']);
    }

    public function testStoreFailure()
    {
        $request = $this->mockRequest(['name' => 'Invalid Department']);
        $this->departmentRepository->shouldReceive('save')->once()->andReturn(null);

        $result = $this->departmentService->store($request);

        $this->assertEquals([
            'message' => 'Department creation was not successful, please try again.',
            'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
        ], $result);
    }

    public function testUpdate()
    {
        $request = $this->mockRequest(['department_id' => 1, 'name' => 'Updated Department']);
        $this->departmentRepository->shouldReceive('updateById')->once()->andReturn(true);

        $result = $this->departmentService->update($request);

        $this->assertEquals(JsonResponse::HTTP_OK, $result['status']);
    }

    public function testUpdateFailure()
    {
        $request = $this->mockRequest(['department_id' => 1, 'name' => 'Invalid Department']);
        $this->departmentRepository->shouldReceive('updateById')->once()->andReturn(false);

        $result = $this->departmentService->update($request);

        $this->assertEquals([
            'message' => 'Department update was not successful, please try again.',
            'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
        ], $result);
    }

    public function testDelete()
    {
        $request = $this->mockRequest(['department_id' => 1]);
        $this->departmentRepository->shouldReceive('deleteById')->once()->andReturn(true);

        $result = $this->departmentService->delete($request);

        $this->assertEquals([
            'message' => 'Department deleted successfully.',
            'status' => JsonResponse::HTTP_OK,
        ], $result);
    }

    public function testDeleteFailure()
    {
        $request = $this->mockRequest(['department_id' => 1]);
        $this->departmentRepository->shouldReceive('deleteById')->once()->andReturn(false);

        $result = $this->departmentService->delete($request);

        $this->assertEquals([
            'message' => 'Department deletion was not successful, please try again.',
            'status' => JsonResponse::HTTP_PRECONDITION_FAILED,
        ], $result);
    }

    private function mockRequest(array $data)
    {
        $request = $this->mock(\Illuminate\Http\Request::class);
        $request->shouldReceive('validated')->andReturn($data);

        return $request;
    }
}
