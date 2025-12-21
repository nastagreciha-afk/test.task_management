<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Task Management API',
    description: 'REST API для управления задачами с JWT аутентификацией и RBAC',
    contact: new OA\Contact(
        email: 'nastagreciha@gmail.com'
    )
)]
#[OA\Server(
    url: 'http://localhost:86/api',
    description: 'Локальный сервер'
)]
#[OA\Server(
    url: 'http://localhost/api',
    description: 'Локальный сервер (без порта)'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Введите JWT токен в формате: Bearer {token}'
)]
class Controller
{
    //
}
