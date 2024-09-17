<?php

namespace Tests\Unit\API\V1\controllers;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Service\V1\Auth\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Response\Fetch\FetchResponse;
use App\Http\Controllers\API\V1\Auth\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerUnitTest extends TestCase
{

    use RefreshDatabase;

    protected $authController;
    protected $authServiceMock;
    protected $fetchResponseHandlerMock;

    public function setUp(): void
    {
        parent::setUp();

        // Mock dependencies if needed
        $this->authServiceMock = $this->createMock(AuthService::class);
        $this->fetchResponseHandlerMock = $this->createMock(FetchResponse::class);

        $this->authController = new AuthController($this->authServiceMock, $this->fetchResponseHandlerMock);
    }

    public function testLoginSuccess()
    {
        // Arrange
        $requestMock = $this->createMock(LoginRequest::class);

        // Mock a successful authentication response from AuthService
        $successfulAuthResponse = ['status' => '200', 'data' => ['user_id' => 1]];

        // Configure the requestMock to return a valid array when validated is called
        $requestMock
            ->expects($this->once())
            ->method('validated')
            ->willReturn(['email' => 'test@example.com', 'password' => 'test_password']);

        $this->authServiceMock
            ->expects($this->once())
            ->method('authenticateUser')
            ->with(['email' => 'test@example.com', 'password' => 'test_password'], $requestMock)
            ->willReturn($successfulAuthResponse);

        $this->fetchResponseHandlerMock
            ->expects($this->once())
            ->method('handleFetchResponse')
            ->with($successfulAuthResponse)
            ->willReturn(response()->json($successfulAuthResponse, JsonResponse::HTTP_OK));

        // Act
        $response = $this->authController->login($requestMock);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testLogoutSuccess()
    {
        // Arrange
        $requestMock = $this->createMock(Request::class);

        // Mock a successful logout response from AuthService
        $successfulLogoutResponse = ['status' => '200', 'message' => 'Logout successful'];

        $this->authServiceMock
            ->expects($this->once())
            ->method('logout')
            ->with($requestMock)
            ->willReturn($successfulLogoutResponse);

        $this->fetchResponseHandlerMock
            ->expects($this->once())
            ->method('handleFetchResponse')
            ->with($successfulLogoutResponse)
            ->willReturn(response()->json($successfulLogoutResponse, JsonResponse::HTTP_OK));

        // Act
        $response = $this->authController->logout($requestMock);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testTokenExchangeSuccess()
    {
        // Arrange
        $requestMock = $this->createMock(Request::class);
        $authController = new AuthController($this->authServiceMock, $this->fetchResponseHandlerMock);

        // Mock a successful token exchange response from AuthService
        $successfulExchangeResponse = ['status' => '200', 'token' => 'new_token'];

        $this->authServiceMock
            ->expects($this->once())
            ->method('tokenExchange')
            ->with($requestMock)
            ->willReturn($successfulExchangeResponse);

        $this->fetchResponseHandlerMock
            ->expects($this->once())
            ->method('handlefetchResponse')
            ->with($successfulExchangeResponse)
            ->willReturn(response()->json($successfulExchangeResponse, JsonResponse::HTTP_OK));

        // Act
        $response = $authController->tokenExhange($requestMock);

        // Assert
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
