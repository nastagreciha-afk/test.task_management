<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_jwt_identifier(): void
    {
        $user = User::factory()->create();

        $this->assertEquals($user->id, $user->getJWTIdentifier());
    }

    public function test_user_returns_empty_jwt_custom_claims(): void
    {
        $user = User::factory()->create();

        $this->assertEquals([], $user->getJWTCustomClaims());
    }

    public function test_user_has_roles_relationship(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->contains($role));
        $this->assertEquals(1, $user->roles->count());
    }

    public function test_has_role_returns_true_when_user_has_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);

        $user->roles()->attach($role);

        $this->assertTrue($user->hasRole('admin'));
    }

    public function test_has_role_returns_false_when_user_does_not_have_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'user']);

        $user->roles()->attach($role);

        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_has_role_returns_false_when_user_has_no_roles(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->hasRole('admin'));
    }
}

