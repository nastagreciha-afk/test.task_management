<?php

namespace Tests\Unit\Policies;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TaskPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();
    }

    public function test_view_any_returns_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_returns_true_for_admin(): void
    {
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $admin->roles()->attach(
            \App\Models\Role::factory()->create(['name' => 'admin'])
        );

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($this->policy->view($admin, $task));
    }

    public function test_view_returns_true_for_task_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->view($user, $task));
    }

    public function test_view_returns_false_for_non_owner_non_admin(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->view($user, $task));
    }

    public function test_create_returns_true(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_returns_true_for_admin(): void
    {
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $admin->roles()->attach(
            \App\Models\Role::factory()->create(['name' => 'admin'])
        );

        $this->assertTrue($this->policy->update($admin, $task));
    }

    public function test_update_returns_true_for_task_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->update($user, $task));
    }

    public function test_update_returns_false_for_non_owner_non_admin(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->update($user, $task));
    }

    public function test_delete_returns_true_for_admin(): void
    {
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $admin->roles()->attach(
            \App\Models\Role::factory()->create(['name' => 'admin'])
        );

        $this->assertTrue($this->policy->delete($admin, $task));
    }

    public function test_delete_returns_true_for_task_owner(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($this->policy->delete($user, $task));
    }

    public function test_delete_returns_false_for_non_owner_non_admin(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($this->policy->delete($user, $task));
    }
}

