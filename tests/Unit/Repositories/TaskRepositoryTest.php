<?php

namespace Tests\Unit\Repositories;

use App\Enums\TaskStatus;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = new TaskRepository();
    }

    public function test_get_tasks_filters_by_user_id_when_user_is_not_admin(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        Task::factory()->create(['user_id' => $user->id]);
        Task::factory()->create(['user_id' => $otherUser->id]);

        Auth::setUser($user);

        $filters = ['per_page' => 15];
        $result = $this->taskRepository->getTasks($filters);

        $this->assertCount(1, $result->items());
        $this->assertEquals($user->id, $result->items()[0]->user_id);
    }

    public function test_get_tasks_does_not_filter_by_user_id_when_user_is_admin(): void
    {
        $admin = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($adminRole);
        
        Task::factory()->create(['user_id' => $admin->id]);
        Task::factory()->create(['user_id' => $otherUser->id]);

        Auth::setUser($admin);

        $filters = ['per_page' => 15];
        $result = $this->taskRepository->getTasks($filters);

        $this->assertCount(2, $result->items());
    }

    public function test_get_tasks_filters_by_status_when_provided(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $admin->roles()->attach($adminRole);
        
        Task::factory()->create(['status' => TaskStatus::PENDING]);
        Task::factory()->create(['status' => TaskStatus::COMPLETED]);
        Task::factory()->create(['status' => TaskStatus::COMPLETED]);

        Auth::setUser($admin);

        $filters = ['status' => 'completed', 'per_page' => 10];
        $result = $this->taskRepository->getTasks($filters);

        $this->assertCount(2, $result->items());
        foreach ($result->items() as $task) {
            $this->assertEquals(TaskStatus::COMPLETED, $task->status);
        }
    }

    public function test_find_returns_task_model(): void
    {
        $task = Task::factory()->create();

        $result = $this->taskRepository->find($task->id);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($task->id, $result->id);
    }

    public function test_create_task_returns_created_task_model(): void
    {
        $user = User::factory()->create();
        $taskData = [
            'title' => 'New Task',
            'description' => 'Description',
            'status' => 'pending',
        ];

        Auth::setUser($user);

        $result = $this->taskRepository->createTask($taskData);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('New Task', $result->title);
        $this->assertEquals($user->id, $result->user_id);
    }

    public function test_update_task_returns_updated_task_model(): void
    {
        $task = Task::factory()->create(['title' => 'Original Title']);
        $taskData = ['title' => 'Updated Task'];

        $result = $this->taskRepository->updateTask($task, $taskData);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals('Updated Task', $result->title);
        $this->assertEquals($task->id, $result->id);
    }

    public function test_delete_task_deletes_task(): void
    {
        $task = Task::factory()->create();
        $taskId = $task->id;

        $this->taskRepository->deleteTask($task);

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }
}

