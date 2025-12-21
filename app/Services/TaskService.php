<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    public function getTasks(array $filters): LengthAwarePaginator
    {
        return $this->taskRepository->getTasks($filters);
    }

    public function getTask(int $id): array
    {
        $task = $this->taskRepository->find($id);
        Gate::authorize('view', $task);

        return $task->toArray();
    }

    public function createTask(array $data): array
    {
        Gate::authorize('create', Task::class);

        $task = $this->taskRepository->createTask($data);
        return $task->toArray();
    }

    public function updateTask(int $id, array $data): array
    {
        $task = $this->taskRepository->find($id);
        Gate::authorize('update', $task);

        $task = $this->taskRepository->updateTask($task, $data);
        return $task->toArray();
    }

    public function deleteTask(int $id): void
    {
        $task = $this->taskRepository->find($id);
        Gate::authorize('delete', $task);

        $this->taskRepository->deleteTask($task);
    }
}


