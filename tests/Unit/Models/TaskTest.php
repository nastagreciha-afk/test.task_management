<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    public function test_task_has_fillable_fields(): void
    {
        $task = new Task();

        $fillable = ['title', 'description', 'status', 'user_id'];

        $this->assertEquals($fillable, $task->getFillable());
    }

    public function test_task_can_be_created(): void
    {
        $user = User::factory()->create();
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $user->id,
        ];

        $task = Task::create($taskData);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Test Task',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);
    }

    public function test_task_can_be_updated(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $task->update(['status' => 'completed']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }
}

