<?php

namespace Tests\Unit\API\V1\controllers;

use Tests\TestCase;
use App\Service\V1\Employee\EmployeeService;
use App\Service\V1\Otp\OtpService;
use App\Service\V1\Auth\AuthService;
use App\Http\Controllers\API\V1\Auth\UpdateUserInfoController;
use App\Http\Response\Update\UpdateResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\sendOTPRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateUserInfoControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $employeeServiceMock;
    protected $otpServiceMock;
    protected $authServiceMock;
    protected $updateResponseHandlerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeServiceMock = $this->createMock(EmployeeService::class);
        $this->otpServiceMock = $this->createMock(OtpService::class);
        $this->authServiceMock = $this->createMock(AuthService::class);
        $this->updateResponseHandlerMock = $this->createMock(UpdateResponse::class);
    }

    public function testSendOTPCodeSuccess()
    {
        // Arrange
        $requestMock = $this->createMock(sendOTPRequest::class);
        $controller = new UpdateUserInfoController($this->employeeServiceMock, $this->otpServiceMock, $this->authServiceMock, $this->updateResponseHandlerMock);

        $requestMock
            ->expects($this->once())
            ->method('validated')
            ->willReturn(['email' => 'test@example.com']);

        // Mock a successful OTP response from OtpService
        $otpResponse = ['status' => '200', 'message' => 'OTP sent successfully'];

        $this->otpServiceMock
            ->expects($this->once())
            ->method('sendOTP')
            ->with(['email' => 'test@example.com'], $requestMock)
            ->willReturn(['status' => '200', 'message' => 'OTP sent successfully']);

        $this->updateResponseHandlerMock
            ->expects($this->once())
            ->method('handleUpdateResponse')
            ->with($otpResponse)
            ->willReturn($otpResponse);

        // Act
        $response = $controller->sendOTPcode($requestMock);

        // Assert
        $this->assertEquals($otpResponse, $response); // Assuming handleUpdateResponse directly returns the response array
    }
}
