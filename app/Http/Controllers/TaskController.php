<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
    }

    #[OA\Get(
        path: '/tasks',
        summary: 'Получить список задач',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                description: 'Фильтр по статусу',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['pending', 'in_progress', 'completed'])
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Номер страницы',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Количество элементов на странице',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 15)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список задач с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'title', type: 'string', example: 'Новая задача'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Описание задачи'),
                                    new OA\Property(property: 'status', type: 'string', example: 'pending'),
                                    new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ]
                            )
                        ),
                        new OA\Property(property: 'total', type: 'integer', example: 10),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Неавторизован'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getTasks($request->all());

        return response()->json($tasks);
    }

    #[OA\Post(
        path: '/tasks',
        summary: 'Создать новую задачу',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'status'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Новая задача'),
                    new OA\Property(property: 'description', type: 'string', example: 'Описание задачи', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'pending'),
                ]
            )
        ),
        tags: ['Tasks'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Задача успешно создана',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Новая задача'),
                        new OA\Property(property: 'description', type: 'string', example: 'Описание задачи'),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'user_id', type: 'integer', example: 1),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Неавторизован'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(TaskStoreRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());

        return response()->json($task, 201);
    }

    #[OA\Get(
        path: '/tasks/{id}',
        summary: 'Получить задачу по ID',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Информация о задаче',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Новая задача'),
                        new OA\Property(property: 'description', type: 'string', example: 'Описание задачи'),
                        new OA\Property(property: 'status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'user_id', type: 'integer', example: 1),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Неавторизован'),
            new OA\Response(response: 403, description: 'Доступ запрещен'),
            new OA\Response(response: 404, description: 'Задача не найдена'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTask($id);

        return response()->json($task);
    }

    #[OA\Put(
        path: '/tasks/{id}',
        summary: 'Обновить задачу',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Обновленная задача'),
                    new OA\Property(property: 'description', type: 'string', example: 'Новое описание', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['pending', 'in_progress', 'completed'], example: 'in_progress'),
                ]
            )
        ),
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача успешно обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'title', type: 'string', example: 'Обновленная задача'),
                        new OA\Property(property: 'description', type: 'string', example: 'Новое описание'),
                        new OA\Property(property: 'status', type: 'string', example: 'in_progress'),
                        new OA\Property(property: 'user_id', type: 'integer', example: 1),
                        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Неавторизован'),
            new OA\Response(response: 403, description: 'Доступ запрещен'),
            new OA\Response(response: 404, description: 'Задача не найдена'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function update(TaskUpdateRequest $request, int $id): JsonResponse
    {
        $task = $this->taskService->updateTask($id, $request->validated());

        return response()->json($task);
    }

    #[OA\Delete(
        path: '/tasks/{id}',
        summary: 'Удалить задачу',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID задачи',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Задача успешно удалена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Task deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Неавторизован'),
            new OA\Response(response: 403, description: 'Доступ запрещен'),
            new OA\Response(response: 404, description: 'Задача не найдена'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $this->taskService->deleteTask($id);

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }
}


