<?php
//AuthRepositoryUnitTest
namespace Tests\Unit\API\V1\repository\auth;

use App\Models\V1\User\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Mockery;
use App\Repository\V1\Auth\AuthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthRepositoryUnitTest extends TestCase
{

    use RefreshDatabase;

    protected $authRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authRepository = new AuthRepository();
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }
    /**
     * Test finding a user by email.
     *
     * @return void
     */
    public function testUnitAuthRepositoryUnitTestfindByemail()
    {
        $user = User::factory()->create();

        // Call the findByemail method
        $result = $this->authRepository->findByemail($user->email);

        // Assert that the result is an instance of User
        $this->assertInstanceOf(User::class, $result);

        // Additional assertions
        $this->assertEquals($user->email, $result['email']);
    }

    /**
     * Test finding a user by non-existent email.
     *
     * @return void
     */
    public function testUnitAuthRepositoryUnitTestFindUserByNonExistentEmail()
    {
        // Call the findByemail method with a non-existent email
        $result = $this->authRepository->findByemail('nonexistent@example.com');

        // Assert that the result is either an instance of ModelNotFoundException or an empty result
        $this->assertTrue($result instanceof ModelNotFoundException || empty($result));
    }


    public function testUpdatePassword()
    {
        // Create a user with a known email and initial password
        $user = User::factory()->create();

        // New password to be set
        $newPassword = 'new_password';

        // Call the method to update the password
        $result = $this->authRepository->updatePassword($user->email, $newPassword);

        // Retrieve the user after the update
        $updatedUser = User::where('email', $user->email)->first();

        // Assert that the method returns true and the user's password is updated
        $this->assertTrue($result);
        $this->assertTrue(Hash::check($newPassword, $updatedUser->password));
    }
}
