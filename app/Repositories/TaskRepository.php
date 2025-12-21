<?php

namespace App\Repositories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskRepository
{
    public function getTasks(array $filters): LengthAwarePaginator
    {
        $query = Task::query();

        /** @var User $user */
        $user = auth()->user();
        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if (isset($filters['status'])) {
            $status = TaskStatus::tryFrom($filters['status']);
            if ($status) {
                $query->where('status', $status);
            }
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function find(int $id): Task
    {
        return Task::findOrFail($id);
    }

    public function createTask(array $data): Task
    {
        $data['user_id'] = auth()->id();

        return Task::create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }
}


