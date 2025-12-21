# Task Management REST API

REST API для управления задачами с JWT аутентификацией и RBAC (Role-Based Access Control).

## 🛠 Технологический стек

- PHP 8+, Laravel 9+
- MySQL/MariaDB (или SQLite)
- JWT (tymon/jwt-auth) для аутентификации
- Swagger/OpenAPI для API документации
- Docker Compose (Laravel Sail)

## 🏗 Архитектура

Проект следует принципам **SOLID**, **DRY**, **KISS** и стандартам **PSR-12/PSR-4**.

**Слои:** `Controller → Service → Repository → Model`

## 🚀 Быстрый старт

```bash
# 1. Клонирование и установка
git clone https://github.com/nastagreciha-afk/test-task-management.git
cd test-task-management
cp .env.example .env

# 1.1. Установка зависимостей
# Если у вас нет Composer локально:
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  composer:2 \
  sh -c "composer config audit.block-insecure false && composer install"

# Или если Composer установлен локально:
composer install

# 2. Запуск через Docker
./vendor/bin/sail up -d

# if you have error
rm -rf storage/bootstrap/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p bootstrap/cache

# 3. Генерация ключей
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan jwt:secret

# 4. Настройка базы данных
# Файл .env.example уже содержит правильные настройки для Docker:
# DB_CONNECTION=mysql
# DB_HOST=mysql
# DB_DATABASE=laravel
# DB_USERNAME=sail
# DB_PASSWORD=password
# Если используете .env.example, настройки уже правильные

# 5. Миграции и сидеры
./vendor/bin/sail artisan migrate --seed

# 6. Swagger (опционально)
mkdir -p storage/api-docs && chmod -R 775 storage/api-docs
./vendor/bin/sail artisan l5-swagger:generate
```

bash test-api.sh http://localhost/api - Запуск автоматического теста ответов API

После запуска:
- API: `http://localhost/api`
- Swagger UI: `http://localhost/api/documentation`

## 👤 Тестовые пользователи

После выполнения `db:seed` создаются следующие пользователи:

**Администратор:**
- **Admin**: `admin@example.com` / `password` (роль: admin)

**Обычные пользователи:**
- **User**: `user@example.com` / `password` (роль: user)
- **John Doe**: `john@example.com` / `password` (роль: user)
- **Jane Smith**: `jane@example.com` / `password` (роль: user)

> Все пользователи имеют пароль: `password`

## 📡 API Endpoints

### Аутентификация

| Метод | Endpoint | Описание | Auth |
|-------|----------|----------|------|
| POST | `/api/login` | Вход в систему | ❌ |
| GET | `/api/me` | Текущий пользователь | ✅ |
| POST | `/api/logout` | Выход | ✅ |

### Задачи

| Метод | Endpoint | Описание |
|-------|----------|----------|
| GET | `/api/tasks` | Список задач (пагинация, фильтр по статусу) |
| POST | `/api/tasks` | Создать задачу |
| GET | `/api/tasks/{id}` | Получить задачу |
| PUT/PATCH | `/api/tasks/{id}` | Обновить задачу (PUT - полное обновление, PATCH - частичное) |
| DELETE | `/api/tasks/{id}` | Удалить задачу |

**Все защищенные endpoints требуют JWT токен в заголовке:** `Authorization: Bearer {token}`

> **Примечание:** Только `/api/login` не требует авторизации. Все остальные endpoints (`/api/me`, `/api/logout`, `/api/tasks/*`) требуют валидный JWT токен.

## 📝 Примеры использования CURL

### 1. Получение токена

```bash
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

**Ответ:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer"
}
```

### 2. Создание задачи

```bash
curl -X POST http://localhost/api/tasks \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Новая задача","description":"Описание","status":"pending"}'
```

### 3. Список задач с фильтрацией

```bash
# С пагинацией
curl -X GET "http://localhost/api/tasks?page=1&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN"

# С фильтром по статусу
curl -X GET "http://localhost/api/tasks?status=completed" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Обновление задачи

```bash
# Полное обновление (PUT)
curl -X PUT http://localhost/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Обновленная задача","description":"Новое описание","status":"in_progress"}'

# Частичное обновление (PATCH)
curl -X PATCH http://localhost/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"completed"}'
```

### 5. Удаление задачи

```bash
curl -X DELETE http://localhost/api/tasks/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🔒 RBAC (Роли и права доступа)

- **Admin** - полный доступ ко всем задачам
- **User** - доступ только к своим задачам

Проверка прав реализована через Policies (`app/Policies/TaskPolicy.php`).

## 📊 Статусы задач

- `pending` - ожидает выполнения
- `in_progress` - в процессе
- `completed` - завершена

## 📊 Формат ошибок

Все ошибки возвращаются в формате JSON:

```json
{
  "message": "Описание ошибки",
  "errors": {
    "field": ["Сообщение об ошибке"]
  }
}
```

**HTTP статус-коды:**
- `200` - успех
- `201` - создано
- `401` - неавторизован
- `403` - доступ запрещен
- `404` - не найдено
- `422` - ошибка валидации

## 📁 Структура проекта

```
app/
├── Http/
│   ├── Controllers/     # Контроллеры
│   └── Requests/        # Валидация
├── Models/              # Eloquent модели
├── Services/            # Бизнес-логика
├── Repositories/        # Работа с данными
├── Policies/            # Проверка прав доступа
└── Enums/               # Enum статусов
```

## 📄 Лицензия

MIT
