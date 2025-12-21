<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_has_users_relationship(): void
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create();

        $role->users()->attach($user);

        $this->assertTrue($role->users->contains($user));
        $this->assertEquals(1, $role->users->count());
    }

    public function test_role_can_be_created(): void
    {
        $role = Role::create(['name' => 'admin']);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'admin',
        ]);
    }

    public function test_role_has_fillable_name(): void
    {
        $role = new Role();

        $this->assertEquals(['name'], $role->getFillable());
    }
}

