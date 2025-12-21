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
    ) {
    }

    public function getTasks(array $filters): LengthAwarePaginator
    {
        return $this->taskRepository->getTasks($filters);
    }

    public function getTask(int $id): array
    {
        $task = Task::findOrFail($id);
        Gate::authorize('view', $task);

        return $this->taskRepository->getTask($id);
    }

    public function createTask(array $data): array
    {
        Gate::authorize('create', Task::class);

        return $this->taskRepository->createTask($data);
    }

    public function updateTask(int $id, array $data): array
    {
        $task = Task::findOrFail($id);
        Gate::authorize('update', $task);

        return $this->taskRepository->updateTask($id, $data);
    }

    public function deleteTask(int $id): void
    {
        $task = Task::findOrFail($id);
        Gate::authorize('delete', $task);

        $this->taskRepository->deleteTask($id);
    }
}

