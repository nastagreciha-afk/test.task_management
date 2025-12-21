# Task Management REST API

REST API для управления задачами с JWT аутентификацией и RBAC (Role-Based Access Control).

## 📋 Содержание

- [Технологический стек](#технологический-стек)
- [Архитектура проекта](#архитектура-проекта)
- [Установка и настройка](#установка-и-настройка)
- [Настройка JWT](#настройка-jwt)
- [Запуск миграций и сидеров](#запуск-миграций-и-сидеров)
- [Swagger/OpenAPI Документация](#swaggeropenapi-документация)
- [API Endpoints](#api-endpoints)
- [Работа с JWT токеном](#работа-с-jwt-токеном)
- [Примеры использования API](#примеры-использования-api)
- [RBAC (Роли и права доступа)](#rbac-роли-и-права-доступа)
- [Структура проекта](#структура-проекта)

## 🛠 Технологический стек

- **PHP 8+**
- **Laravel 9+**
- **MySQL/MariaDB** (или SQLite для упрощения)
- **JWT (tymon/jwt-auth)** для аутентификации
- **Swagger/OpenAPI (darkaonline/l5-swagger)** для API документации
- **Docker Compose** (Laravel Sail) для быстрого запуска

## 🏗 Архитектура проекта

Проект следует принципам **SOLID**, **DRY**, **KISS** и стандартам **PSR-12/PSR-4**.

### Слои архитектуры:

```
Controller → Service → Repository → Model
```

1. **Controllers** (`app/Http/Controllers/`) - обрабатывают HTTP запросы
2. **Services** (`app/Services/`) - бизнес-логика приложения
3. **Repositories** (`app/Repositories/`) - работа с данными через Eloquent
4. **Models** (`app/Models/`) - Eloquent модели
5. **Policies** (`app/Policies/`) - проверка прав доступа (RBAC)
6. **Requests** (`app/Http/Requests/`) - валидация входящих данных

## 📦 Установка и настройка

### 1. Клонирование репозитория

```bash
git clone https://github.com/nastagreciha-afk/test.task_management.git
cd test.task_management
```

### 2. Копирование файла окружения

```bash
cp .env.example .env
```

### 3. Установка зависимостей

Если у вас нет установленного Composer локально:

```bash
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  composer:2 \
  sh -c "composer config audit.block-insecure false && composer install"
```

Или если Composer установлен локально:

```bash
composer install
```

### 3.1. Настройка Swagger (опционально, но рекомендуется)

После установки зависимостей выполните:

```bash
./vendor/bin/sail artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

Это создаст конфигурационный файл `config/l5-swagger.php` (уже создан в проекте).

Создайте директорию для документации:

```bash
mkdir -p storage/api-docs
chmod -R 775 storage/api-docs
```

Сгенерируйте документацию:

```bash
./vendor/bin/sail artisan l5-swagger:generate
```

### 4. Запуск через Docker Compose (Laravel Sail)

```bash
./vendor/bin/sail up -d
```

### 5. Генерация ключа приложения

```bash
./vendor/bin/sail artisan key:generate
```

### 6. Исправление прав доступа (если необходимо)

Если возникла ошибка `file_put_contents(...sessions/...): Failed to open stream`:

```bash
rm -rf storage/bootstrap/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p bootstrap/cache

sudo chown -R $USER:$USER storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 7. Настройка базы данных в `.env`

Откройте файл `.env` и настройте подключение к базе данных:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

## 🔐 Настройка JWT

### 1. Публикация конфигурации JWT

```bash
./vendor/bin/sail artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

### 2. Генерация секретного ключа JWT

```bash
./vendor/bin/sail artisan jwt:secret
```

Эта команда добавит `JWT_SECRET` в ваш `.env` файл.

### 3. Настройка в `.env` (опционально)

Вы можете настроить время жизни токена в `config/jwt.php` или через переменные окружения:

```env
JWT_TTL=60  # время жизни токена в минутах (по умолчанию 60)
```

## 🗄 Запуск миграций и сидеров

### Запуск миграций

```bash
./vendor/bin/sail artisan migrate
```

### Запуск сидеров (создание ролей и тестовых пользователей)

```bash
./vendor/bin/sail artisan db:seed
```

После выполнения сидеров будут созданы:

**Роли:**
- `admin` - администратор
- `user` - обычный пользователь

**Тестовые пользователи:**
- **Admin**: `admin@example.com` / `password`
- **User**: `user@example.com` / `password`

## 📚 Swagger/OpenAPI Документация

После установки и настройки Swagger, документация API будет доступна по адресу:

```
http://localhost/api/documentation
```

(или `http://localhost:86/api/documentation` если используется порт 86)

### Доступ к Swagger UI

1. Откройте браузер и перейдите по адресу `/api/documentation`
2. В интерфейсе Swagger UI вы можете:
   - Просмотреть все доступные endpoints
   - Увидеть параметры запросов и ответов
   - Протестировать API прямо из браузера
   - Авторизоваться с помощью JWT токена (кнопка "Authorize")

### Авторизация в Swagger UI

1. Получите JWT токен через endpoint `/api/login`
2. В Swagger UI нажмите кнопку **"Authorize"** (справа вверху)
3. Введите токен в формате: `Bearer {your_token_here}` или просто `{your_token_here}`
4. Нажмите "Authorize" и "Close"
5. Теперь все защищенные endpoints будут использовать ваш токен

### Регенерация документации

После изменения аннотаций в контроллерах, обновите документацию:

```bash
./vendor/bin/sail artisan l5-swagger:generate
```

Или установите автоматическую регенерацию в `.env`:

```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

## 📡 API Endpoints

### Базовый URL

```
http://localhost/api
```

(или `http://localhost:86/api` если используется порт 86)

### Аутентификация

| Метод | Endpoint | Описание | Auth |
|-------|----------|-----------|------|
| POST | `/api/login` | Вход в систему | ❌ |
| GET | `/api/me` | Получить текущего пользователя | ✅ |
| POST | `/api/logout` | Выход из системы | ✅ |

### Задачи (Tasks)

| Метод | Endpoint | Описание | Auth |
|-------|----------|-----------|------|
| GET | `/api/tasks` | Список задач (с пагинацией и фильтрацией) | ✅ |
| POST | `/api/tasks` | Создать задачу | ✅ |
| GET | `/api/tasks/{id}` | Получить задачу по ID | ✅ |
| PUT/PATCH | `/api/tasks/{id}` | Обновить задачу | ✅ |
| DELETE | `/api/tasks/{id}` | Удалить задачу | ✅ |

## 🔑 Работа с JWT токеном

### 1. Получение токена

Для получения JWT токена необходимо выполнить запрос на `/api/login`:

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Ответ:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer"
}
```

### 2. Использование токена в запросах

Все защищенные endpoints требуют JWT токен в заголовке `Authorization`:

```
Authorization: Bearer {your_token_here}
```

**Пример:**
```bash
curl -X GET http://localhost/api/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### 3. Выход из системы

Для выхода из системы (инвалидации токена):

```bash
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer {your_token_here}"
```

## 📝 Примеры использования API

### Пример 1: Вход в систему и получение токена

```bash
# Вход как администратор
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

**Ответ:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9sb2dpbiIsImlhdCI6MTcwMzEyMzQ1NiwiZXhwIjoxNzAzMTI3MDU2LCJzdWIiOiIxIiwicm9sZSI6ImFkbWluIn0.xxx",
  "token_type": "bearer"
}
```

Сохраните `access_token` для последующих запросов.

### Пример 2: Получение информации о текущем пользователе

```bash
# Замените YOUR_TOKEN на токен из предыдущего примера
curl -X GET http://localhost/api/me \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Ответ:**
```json
{
  "id": 1,
  "name": "Admin",
  "email": "admin@example.com",
  "created_at": "2024-01-01T00:00:00.000000Z",
  "updated_at": "2024-01-01T00:00:00.000000Z"
}
```

### Пример 3: Создание задачи

```bash
curl -X POST http://localhost/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Новая задача",
    "description": "Описание задачи",
    "status": "pending"
  }'
```

**Ответ (201 Created):**
```json
{
  "id": 1,
  "title": "Новая задача",
  "description": "Описание задачи",
  "status": "pending",
  "user_id": 1,
  "created_at": "2024-01-01T12:00:00.000000Z",
  "updated_at": "2024-01-01T12:00:00.000000Z"
}
```

### Пример 4: Получение списка задач с пагинацией

```bash
curl -X GET "http://localhost/api/tasks?page=1&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Ответ:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Новая задача",
      "description": "Описание задачи",
      "status": "pending",
      "user_id": 1,
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    }
  ],
  "first_page_url": "http://localhost/api/tasks?page=1",
  "from": 1,
  "last_page": 1,
  "last_page_url": "http://localhost/api/tasks?page=1",
  "links": [...],
  "next_page_url": null,
  "path": "http://localhost/api/tasks",
  "per_page": 10,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

### Пример 5: Фильтрация задач по статусу

```bash
curl -X GET "http://localhost/api/tasks?status=completed" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Пример 6: Получение задачи по ID

```bash
curl -X GET http://localhost/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Ответ:**
```json
{
  "id": 1,
  "title": "Новая задача",
  "description": "Описание задачи",
  "status": "pending",
  "user_id": 1,
  "created_at": "2024-01-01T12:00:00.000000Z",
  "updated_at": "2024-01-01T12:00:00.000000Z"
}
```

### Пример 7: Обновление задачи

```bash
curl -X PUT http://localhost/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Обновленная задача",
    "status": "in_progress"
  }'
```

**Ответ:**
```json
{
  "id": 1,
  "title": "Обновленная задача",
  "description": "Описание задачи",
  "status": "in_progress",
  "user_id": 1,
  "created_at": "2024-01-01T12:00:00.000000Z",
  "updated_at": "2024-01-01T12:05:00.000000Z"
}
```

### Пример 8: Удаление задачи

```bash
curl -X DELETE http://localhost/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Ответ (200 OK):**
```json
{
  "message": "Task deleted successfully"
}
```

## 🔒 RBAC (Роли и права доступа)

### Роли

1. **Admin** (`admin`)
   - Полный доступ ко всем задачам (своим и чужим)
   - Может создавать, читать, обновлять и удалять любые задачи

2. **User** (`user`)
   - Доступ только к своим задачам
   - Может создавать свои задачи
   - Может читать, обновлять и удалять только свои задачи

### Логика проверки прав

Проверка прав доступа реализована через **Policies** (`app/Policies/TaskPolicy.php`):

- **Admin** может выполнять любые операции с любыми задачами
- **User** может работать только со своими задачами (`task->user_id === user->id`)

### Примеры работы с разными ролями

**Как Admin:**
```bash
# Получить токен администратора
TOKEN=$(curl -s -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.access_token')

# Получить все задачи (включая чужие)
curl -X GET http://localhost/api/tasks \
  -H "Authorization: Bearer $TOKEN"
```

**Как User:**
```bash
# Получить токен обычного пользователя
TOKEN=$(curl -s -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.access_token')

# Получить только свои задачи
curl -X GET http://localhost/api/tasks \
  -H "Authorization: Bearer $TOKEN"
```

## 📁 Структура проекта

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Контроллер аутентификации
│   │   └── TaskController.php      # Контроллер задач
│   └── Requests/
│       ├── LoginRequest.php        # Валидация логина
│       ├── TaskStoreRequest.php    # Валидация создания задачи
│       └── TaskUpdateRequest.php   # Валидация обновления задачи
├── Models/
│   ├── User.php                    # Модель пользователя (с JWT)
│   ├── Role.php                    # Модель роли
│   └── Task.php                    # Модель задачи
├── Services/
│   ├── AuthService.php            # Сервис аутентификации
│   └── TaskService.php            # Сервис задач
├── Repositories/
│   └── TaskRepository.php         # Репозиторий задач
└── Policies/
    └── TaskPolicy.php             # Политика доступа к задачам

database/
├── migrations/
│   ├── 2024_01_01_000001_create_roles_table.php
│   ├── 2024_01_01_000002_create_role_user_table.php
│   └── 2024_01_01_000003_create_tasks_table.php
└── seeders/
    ├── RoleSeeder.php             # Создание ролей
    └── UserSeeder.php             # Создание тестовых пользователей

routes/
└── api.php                        # API маршруты
```

## 🔄 Изменения в проекте

### Добавленные компоненты

1. **Аутентификация через JWT**
   - `AuthController` - обработка логина, получения текущего пользователя и выхода
   - `AuthService` - бизнес-логика аутентификации
   - `LoginRequest` - валидация данных входа
   - Обновлена модель `User` для поддержки JWT (`JWTSubject`)

2. **Управление задачами**
   - `TaskController` - CRUD операции для задач
   - `TaskService` - бизнес-логика работы с задачами
   - `TaskRepository` - работа с данными задач
   - `TaskStoreRequest` и `TaskUpdateRequest` - валидация данных

3. **RBAC система**
   - Модель `Role` и связь many-to-many с `User`
   - `TaskPolicy` - проверка прав доступа
   - Метод `hasRole()` в модели `User`
   - Фильтрация задач по ролям в `TaskRepository`

4. **Миграции и сидеры**
   - Миграции для таблиц `roles`, `role_user`, `tasks`
   - Сидеры для создания ролей и тестовых пользователей

5. **Конфигурация**
   - Добавлен JWT guard в `config/auth.php`
   - Настроены API маршруты в `routes/api.php`

## 📊 Формат ответов API

### Успешный ответ

Все успешные ответы возвращаются в формате JSON с соответствующими HTTP статус-кодами:

- `200 OK` - успешный запрос
- `201 Created` - ресурс создан
- `204 No Content` - успешное удаление (опционально)

### Ошибки

Формат ошибок соответствует стандарту Laravel:

**Валидация (422 Unprocessable Entity):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."],
    "status": ["The selected status is invalid."]
  }
}
```

**Неавторизован (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

**Доступ запрещен (403 Forbidden):**
```json
{
  "message": "This action is unauthorized."
}
```

**Ресурс не найден (404 Not Found):**
```json
{
  "message": "No query results for model [App\\Models\\Task] 123"
}
```

## 🚀 Быстрый старт

1. Клонируйте репозиторий
2. Скопируйте `.env.example` в `.env`
3. Установите зависимости: `composer install`
4. Запустите Docker: `./vendor/bin/sail up -d`
5. Сгенерируйте ключи: `./vendor/bin/sail artisan key:generate && ./vendor/bin/sail artisan jwt:secret`
6. Запустите миграции и сидеры: `./vendor/bin/sail artisan migrate --seed`
7. Настройте Swagger (опционально):
   - Создайте директорию: `mkdir -p storage/api-docs && chmod -R 775 storage/api-docs`
   - Сгенерируйте документацию: `./vendor/bin/sail artisan l5-swagger:generate`
8. Откройте Swagger UI: `http://localhost/api/documentation`
9. Получите токен: `curl -X POST http://localhost/api/login -H "Content-Type: application/json" -d '{"email":"admin@example.com","password":"password"}'`

## 📝 Статусы задач

Задачи могут иметь следующие статусы:
- `pending` - ожидает выполнения
- `in_progress` - в процессе выполнения
- `completed` - завершена

## 🔧 Дополнительные команды

### Очистка кэша

```bash
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
```

### Регенерация Swagger документации

```bash
./vendor/bin/sail artisan l5-swagger:generate
```

### Перезапуск Docker

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### Просмотр логов

```bash
./vendor/bin/sail logs -f
```

## 📄 Лицензия

MIT
