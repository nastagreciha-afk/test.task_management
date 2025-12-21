<?php

namespace Tests\Unit\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskService $taskService;
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = Mockery::mock(TaskRepository::class);
        $this->taskService = new TaskService($this->taskRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_tasks_returns_paginated_tasks(): void
    {
        $filters = ['status' => 'pending', 'per_page' => 10];
        $expectedPaginator = Mockery::mock(LengthAwarePaginator::class);

        $this->taskRepository
            ->shouldReceive('getTasks')
            ->once()
            ->with($filters)
            ->andReturn($expectedPaginator);

        $result = $this->taskService->getTasks($filters);

        $this->assertSame($expectedPaginator, $result);
    }

    public function test_get_task_returns_task_array_when_authorized(): void
    {
        $taskId = 1;
        $task = Mockery::mock(Task::class);
        $expectedArray = ['id' => 1, 'title' => 'Test Task'];

        $this->taskRepository
            ->shouldReceive('find')
            ->once()
            ->with($taskId)
            ->andReturn($task);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('view', $task)
            ->andReturn(true);

        $task->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedArray);

        $result = $this->taskService->getTask($taskId);

        $this->assertEquals($expectedArray, $result);
    }

    public function test_create_task_returns_task_array_when_authorized(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'pending',
        ];
        $task = Mockery::mock(Task::class);
        $expectedArray = ['id' => 1, 'title' => 'New Task'];

        Gate::shouldReceive('authorize')
            ->once()
            ->with('create', Task::class)
            ->andReturn(true);

        $this->taskRepository
            ->shouldReceive('createTask')
            ->once()
            ->with($taskData)
            ->andReturn($task);

        $task->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedArray);

        $result = $this->taskService->createTask($taskData);

        $this->assertEquals($expectedArray, $result);
    }

    public function test_update_task_returns_updated_task_array_when_authorized(): void
    {
        $taskId = 1;
        $taskData = ['title' => 'Updated Task'];
        $task = Mockery::mock(Task::class);
        $updatedTask = Mockery::mock(Task::class);
        $expectedArray = ['id' => 1, 'title' => 'Updated Task'];

        $this->taskRepository
            ->shouldReceive('find')
            ->once()
            ->with($taskId)
            ->andReturn($task);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $task)
            ->andReturn(true);

        $this->taskRepository
            ->shouldReceive('updateTask')
            ->once()
            ->with($task, $taskData)
            ->andReturn($updatedTask);

        $updatedTask->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedArray);

        $result = $this->taskService->updateTask($taskId, $taskData);

        $this->assertEquals($expectedArray, $result);
    }

    public function test_delete_task_calls_repository_when_authorized(): void
    {
        $taskId = 1;
        $task = Mockery::mock(Task::class);

        $this->taskRepository
            ->shouldReceive('find')
            ->once()
            ->with($taskId)
            ->andReturn($task);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('delete', $task)
            ->andReturn(true);

        $this->taskRepository
            ->shouldReceive('deleteTask')
            ->once()
            ->with($task);

        $this->taskService->deleteTask($taskId);

        $this->assertTrue(true);
    }
}

