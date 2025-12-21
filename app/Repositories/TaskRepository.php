<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function getTasks(array $filters): LengthAwarePaginator
    {
        $query = Task::query();

        $user = auth()->user();
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getTask(int $id): array
    {
        $task = Task::findOrFail($id);

        return $task->toArray();
    }

    public function createTask(array $data): array
    {
        $data['user_id'] = auth()->id();
        $task = Task::create($data);

        return $task->toArray();
    }

    public function updateTask(int $id, array $data): array
    {
        $task = Task::findOrFail($id);
        $task->update($data);

        return $task->toArray();
    }

    public function deleteTask(int $id): void
    {
        $task = Task::findOrFail($id);
        $task->delete();
    }
}

