<?php

namespace Tests\Unit\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class TaskRepositoryTest extends TestCase
{
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = new TaskRepository();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_tasks_filters_by_user_id_when_user_is_not_admin(): void
    {
        $user = Mockery::mock(User::class);
        $user->id = 1;
        $user->shouldReceive('hasRole')
            ->with('admin')
            ->once()
            ->andReturn(false);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $filters = ['per_page' => 15];
        $query = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        Task::shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('where')
            ->with('user_id', 1)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('paginate')
            ->with(15)
            ->once()
            ->andReturn($paginator);

        $result = $this->taskRepository->getTasks($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_tasks_does_not_filter_by_user_id_when_user_is_admin(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')
            ->with('admin')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $filters = ['per_page' => 15];
        $query = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        Task::shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('where')
            ->with('status', 'pending')
            ->never();

        $query->shouldReceive('paginate')
            ->with(15)
            ->once()
            ->andReturn($paginator);

        $result = $this->taskRepository->getTasks($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_tasks_filters_by_status_when_provided(): void
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')
            ->with('admin')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $filters = ['status' => 'completed', 'per_page' => 10];
        $query = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        Task::shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('where')
            ->with('status', 'completed')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('paginate')
            ->with(10)
            ->once()
            ->andReturn($paginator);

        $result = $this->taskRepository->getTasks($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_get_task_returns_task_array(): void
    {
        $taskId = 1;
        $task = Mockery::mock(Task::class);
        $expectedArray = [
            'id' => 1,
            'title' => 'Test Task',
            'status' => 'pending',
        ];

        Task::shouldReceive('findOrFail')
            ->once()
            ->with($taskId)
            ->andReturn($task);

        $task->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedArray);

        $result = $this->taskRepository->getTask($taskId);

        $this->assertEquals($expectedArray, $result);
    }

    public function test_create_task_returns_created_task_array(): void
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Description',
            'status' => 'pending',
        ];
        $expectedArray = ['id' => 1, 'title' => 'New Task'];

        Auth::shouldReceive('id')
            ->once()
            ->andReturn(1);

        $task = Mockery::mock(Task::class);
        $task->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedArray);

        Task::shouldReceive('create')
            ->once()
            ->with(array_merge($taskData, ['user_id' => 1]))
            ->andReturn($task);

        $result = $this->taskRepository->createTask($taskData);

        $this->assertEquals($expectedArray, $result);
    }

    public function test_update_task_returns_updated_task_array(): void
    {
        $taskId = 1;
        $taskData = ['title' => 'Updated Task'];
        $task = Mockery::mock(Task::class);
        $expectedArray = ['id' => 1, 'title' => 'Updated Task'];

        Task::shouldReceive('findOrFail')
            ->once()
            ->with($taskId)
            ->andReturn($task);

        $task->shouldReceive('update')
            ->once()
            ->with($taskData)
            ->andReturn(true);

        $task->shouldReceive('toArray')
            ->once()
            ->andReturn($expectedArray);

        $result = $this->taskRepository->updateTask($taskId, $taskData);

        $this->assertEquals($expectedArray, $result);
    }

    public function test_delete_task_deletes_task(): void
    {
        $taskId = 1;
        $task = Mockery::mock(Task::class);

        Task::shouldReceive('findOrFail')
            ->once()
            ->with($taskId)
            ->andReturn($task);

        $task->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->taskRepository->deleteTask($taskId);
    }
}

