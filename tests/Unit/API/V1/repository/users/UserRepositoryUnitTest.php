<?php

namespace Tests\Unit\API\V1\repository\users;

use Mockery;
use Tests\TestCase;
use App\Models\V1\User\User;
use App\Repository\V1\Users\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserRepositoryUnitTest extends TestCase
{
    use RefreshDatabase;
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository();
    }

    public function testUnitsave()
    {
        $userData= User::factory()->make()->toArray();

        $result = $this->userRepository->save($userData);
        $this->assertInstanceOf(User::class, $result);
    }

    public function testUnitfindByemail()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->findByemail($user->email);
        $this->assertInstanceOf(User::class, $result);
    }

    public function testUnitfindByParam()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->findByParam($user->email);
        $this->assertNotEmpty($result);
    }

    public function testUnitUpdateInitialPassword()
    {
        $user = User::factory()->create();

        // Call the method to update the initial password
        $result = $this->userRepository->updateInitialPassword($user->email, $user->password);

        // Retrieve the user after the update
        $updatedUser = User::where('email', $user->email)->first();

        // Assert that the method returns true and the user's password is updated
        $this->assertEquals(1, $result);
        $this->assertTrue(password_verify($user->password, $updatedUser->password));
    }

    public function testUnitUpdatePassword()
    {
        $password = 'new_password';

        $user = User::factory()->create();

        // Call the method to update the password
        $result = $this->userRepository->updatePassword($user->email, $password);

        // Retrieve the user after the update
        $updatedUser = User::where('email', $user->email)->first();

        // Assert that the method returns true and the user's password is updated
        $this->assertEquals(1, $result);
        $this->assertTrue(password_verify($password, $updatedUser->password));
    }


    public function testUnitfindById()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->findById($user->id);

        $this->assertNotNull($result);
    }


    public function testUnitdeleteByemail()
    {
        $user = User::factory()->create();

        $result = $this->userRepository->deleteByemail($user->email);
        $this->assertTrue($result);
    }
}