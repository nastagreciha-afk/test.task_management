<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'REST API для управления задачами с JWT аутентификацией и RBAC',
    title: 'Task Management API',
    contact: new OA\Contact(
        email: 'nastagreciha@gmail.com'
    )
)]
#[OA\Server(
    url: 'http://localhost/api',
    description: 'Локальный сервер'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Введите JWT токен в формате: Bearer {token}',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class Controller
{
    //
}
