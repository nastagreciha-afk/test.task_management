<?php

namespace Tests\Unit\Services;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_returns_token_when_credentials_are_valid(): void
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password',
        ];
        $expectedToken = 'test-jwt-token';

        $guard = Mockery::mock();
        $guard->shouldReceive('attempt')
            ->with($credentials)
            ->once()
            ->andReturn($expectedToken);

        Auth::shouldReceive('guard')
            ->with('api')
            ->once()
            ->andReturn($guard);

        $result = $this->authService->login($credentials);

        $this->assertEquals($expectedToken, $result);
    }

    public function test_login_returns_null_when_credentials_are_invalid(): void
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ];

        $guard = Mockery::mock();
        $guard->shouldReceive('attempt')
            ->with($credentials)
            ->once()
            ->andReturn(false);

        Auth::shouldReceive('guard')
            ->with('api')
            ->once()
            ->andReturn($guard);

        $result = $this->authService->login($credentials);

        $this->assertNull($result);
    }
}

