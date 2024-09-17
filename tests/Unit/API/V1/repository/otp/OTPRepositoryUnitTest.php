<?php

namespace Tests\Unit\API\V1\repository\otp;

use Tests\TestCase;
use App\Models\V1\Otp\Otp;
use App\Models\V1\User\User;
use App\Repository\V1\Otp\OTPRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OTPRepositoryUnitTest extends TestCase
{

    use RefreshDatabase;

    private $otpRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpRepository = new OTPRepository();
    }

    public function testUnitsave()
    {

        $user = User::factory()->create();

        $otpData = [
            'user_id' => $user->id,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ];

        $result = $this->otpRepository->save($otpData);

        $this->assertInstanceOf(Otp::class, $result);
        $this->assertEquals($otpData['otp'], $result->otp);
    }

    public function testUnitfindByRefId()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10)
        ]);

        $result = $this->otpRepository->findByRefId($user->id);

        $this->assertInstanceOf(Otp::class, $result);
        $this->assertEquals($otp->otp, $result->otp);
        // Add more assertions as needed
    }

    public function testUnitdeleteByUserId()
    {
        $user = User::factory()->create();

        $otp = Otp::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10)
        ]);

        $result = $this->otpRepository->deleteByUserId($user->id);

        $this->assertEquals(1, $result);
        $this->assertDatabaseMissing('otps', ['id' => $otp->id]);
    }

    public function testUnitfindByemail()
    {
        $user = User::factory()->create();

        $result = $this->otpRepository->findByemail($user->email);
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->email, $result->email);
    }
}
