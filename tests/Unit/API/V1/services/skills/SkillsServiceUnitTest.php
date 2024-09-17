<?php

namespace Tests\Unit\API\V1\services\skills;

use Tests\TestCase;
use App\Enums\Roles;
use Illuminate\Support\Arr;
use App\Models\V1\User\User;
use App\Models\V1\skill\Skill;
use Illuminate\Http\JsonResponse;
use App\Models\V1\Employee\Employee;
use App\Service\V1\Skills\SkillsService;
use App\Repository\V1\Employee\EmployeeRepository;
use App\Repository\V1\Skills\SkillsInterfaceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class SkillsServiceUnitTest extends TestCase
{
    use RefreshDatabase;
    protected $skillsRepositoryMock;
    protected $employeeRepositoryMock;
    protected $skillsService;
    private $roles;


    protected function setUp(): void
    {
        parent::setUp();
        $this->skillsRepositoryMock = $this->createMock(SkillsInterfaceRepository::class);
        $this->employeeRepositoryMock = $this->createMock(EmployeeRepository::class);
        $this->skillsService = new SkillsService($this->skillsRepositoryMock, $this->employeeRepositoryMock);
        $this->roles = [Roles::ADMIN->value, Roles::MGT->value];
    }


    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testFetchSkills()
    {
        // Arrange
        $skillsService = new SkillsService($this->skillsRepositoryMock, $this->employeeRepositoryMock);

        // Mock repository response
        $this->skillsRepositoryMock
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([]);

        // Act
        $result = $skillsService->fetch();

        // Assert
        $this->assertEquals(JsonResponse::HTTP_OK, $result['status']);
        $this->assertArrayHasKey('skills', $result);
    }




    public function testStoreSkillSuccess()
    {
        $employee = Employee::factory()->create();

        $response = $this->post('/api/v1/users/login', [
            'email' => $employee['authInfo']['email'],
            'password' => 'password',
        ]);

        $accessToken = $response->json('accessToken');

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ];

        $result = $this->withHeaders($headers)->post(
            '/api/v1/skills/store',
            ['name' => 'football', 'userId' => $employee['id']]
        );

        $result->assertStatus(JsonResponse::HTTP_OK);
    }


    public function testUpdateSkillSuccess()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        //already created
        $skill = Skill::factory()->create()->toArray();

        $fakeSkills = Skill::factory()->make()->toArray();

        $response = $this->post('/api/v1/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $response['accessToken'],
            'Accept' => 'application/json',
        ];
        $result = $this->withHeaders($headers)->put(
            '/api/v1/skills/update',
            ['name' => $fakeSkills['name'], 'skills_id' => $skill['id']]
        );

        $result->assertStatus(JsonResponse::HTTP_OK);
    }
    public function testDeleteSkillSuccess()
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create([
            'userId' => $user['id'],
            'roleId' => Roles::getRoleIdByValue( Arr::random($this->roles)),
        ]);

        //already created
        $skill = Skill::factory()->create()->toArray();

        $response = $this->post('/api/v1/users/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $headers = [
            'Authorization' => 'Bearer ' . $response['accessToken'],
            'Accept' => 'application/json',
        ];
        $result = $this->withHeaders($headers)->delete(
            '/api/v1/skills/delete',
            ['name' => $skill['name'], 'skills_id' => $skill['id']]
        );

        $result->assertStatus(JsonResponse::HTTP_OK);
    }
}