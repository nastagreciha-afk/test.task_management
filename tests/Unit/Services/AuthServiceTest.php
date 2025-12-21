<?php

namespace Tests\Unit\Services;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_login_returns_token_when_credentials_are_valid(): void
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];
        $expectedToken = 'test-jwt-token';

        Auth::shouldReceive('guard')
            ->with('api')
            ->once()
            ->andReturnSelf();

        Auth::shouldReceive('attempt')
            ->with($credentials)
            ->once()
            ->andReturn($expectedToken);

        $result = $this->authService->login($credentials);

        $this->assertEquals($expectedToken, $result);
    }

    public function test_login_returns_null_when_credentials_are_invalid(): void
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ];

        Auth::shouldReceive('guard')
            ->with('api')
            ->once()
            ->andReturnSelf();

        Auth::shouldReceive('attempt')
            ->with($credentials)
            ->once()
            ->andReturn(false);

        $result = $this->authService->login($credentials);

        $this->assertNull($result);
    }
}

