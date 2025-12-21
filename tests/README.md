# Unit Tests

Набор unit тестов для проверки изолированных компонентов приложения.

## Структура тестов

```
tests/
├── Unit/
│   ├── Services/
│   │   ├── AuthServiceTest.php      # Тесты сервиса аутентификации
│   │   └── TaskServiceTest.php      # Тесты сервиса задач
│   ├── Repositories/
│   │   └── TaskRepositoryTest.php   # Тесты репозитория задач
│   ├── Models/
│   │   ├── UserTest.php             # Тесты модели User
│   │   ├── TaskTest.php             # Тесты модели Task
│   │   └── RoleTest.php             # Тесты модели Role
│   └── Policies/
│       └── TaskPolicyTest.php       # Тесты политики доступа
```

## Запуск тестов

### Запуск всех unit тестов

```bash
./vendor/bin/sail artisan test --testsuite=Unit
```

или

```bash
./vendor/bin/sail phpunit --testsuite=Unit
```

### Запуск конкретного теста

```bash
./vendor/bin/sail artisan test tests/Unit/Services/AuthServiceTest.php
```

### Запуск с покрытием кода

```bash
./vendor/bin/sail artisan test --coverage
```

## Описание тестов

### AuthServiceTest

Тестирует сервис аутентификации:
- Успешный логин с валидными учетными данными
- Неудачный логин с невалидными учетными данными

**Использует моки:** `Auth` facade

### TaskServiceTest

Тестирует сервис задач:
- Получение списка задач с фильтрацией
- Получение задачи по ID
- Создание задачи
- Обновление задачи
- Удаление задачи

**Использует моки:** `TaskRepository`, `Gate`, `Task` model

### TaskRepositoryTest

Тестирует репозиторий задач:
- Фильтрация задач по user_id для обычных пользователей
- Отсутствие фильтрации для администраторов
- Фильтрация по статусу
- Получение задачи по ID
- Создание задачи
- Обновление задачи
- Удаление задачи

**Использует моки:** `Task` model, `Auth` facade, `User` model

### UserTest

Тестирует модель User:
- JWT идентификатор
- JWT кастомные claims
- Связь с ролями (many-to-many)
- Метод `hasRole()`

**Использует базу данных:** Да (RefreshDatabase)

### TaskTest

Тестирует модель Task:
- Связь с пользователем (belongsTo)
- Fillable поля
- Создание задачи
- Обновление задачи

**Использует базу данных:** Да (RefreshDatabase)

### RoleTest

Тестирует модель Role:
- Связь с пользователями (many-to-many)
- Создание роли
- Fillable поля

**Использует базу данных:** Да (RefreshDatabase)

### TaskPolicyTest

Тестирует политику доступа к задачам:
- `viewAny()` - всегда возвращает true
- `view()` - проверка прав для admin и владельца
- `create()` - всегда возвращает true
- `update()` - проверка прав для admin и владельца
- `delete()` - проверка прав для admin и владельца

**Использует базу данных:** Да (RefreshDatabase)

## Зависимости

Тесты используют:
- **PHPUnit** - фреймворк для тестирования
- **Mockery** - библиотека для создания моков
- **Laravel Factories** - для создания тестовых данных

## Примечания

1. Тесты, использующие базу данных, используют `RefreshDatabase` trait для очистки БД после каждого теста
2. Unit тесты изолированы и не зависят от внешних сервисов благодаря использованию моков
3. Для тестов моделей используется реальная база данных (SQLite в памяти для тестов)

## Примеры использования моков

```php
// Мок Auth facade
Auth::shouldReceive('guard')
    ->with('api')
    ->once()
    ->andReturnSelf();

// Мок репозитория
$repository = Mockery::mock(TaskRepository::class);
$repository->shouldReceive('getTasks')
    ->once()
    ->with($filters)
    ->andReturn($expectedResult);
```

