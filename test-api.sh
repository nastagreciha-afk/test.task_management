#!/bin/bash

# Скрипт для тестирования API endpoints
# Использование: ./test-api.sh [base_url]
# Пример: ./test-api.sh http://localhost/api

BASE_URL="${1:-http://localhost/api}"
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}=== Тестирование API: $BASE_URL ===${NC}\n"

# Функция для вывода результатов
print_result() {
    local test_name=$1
    local status_code=$2
    local expected=$3
    
    if [ "$status_code" == "$expected" ]; then
        echo -e "${GREEN}✓${NC} $test_name: $status_code (ожидалось $expected)"
    else
        echo -e "${RED}✗${NC} $test_name: $status_code (ожидалось $expected)"
    fi
}

# 1. Логин админа
echo -e "${YELLOW}--- Тест 1: Логин администратора ---${NC}"
ADMIN_TOKEN=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r '.access_token // empty')

if [ -z "$ADMIN_TOKEN" ] || [ "$ADMIN_TOKEN" == "null" ]; then
    echo -e "${RED}✗ Не удалось получить токен администратора${NC}"
    exit 1
else
    echo -e "${GREEN}✓${NC} Токен администратора получен"
fi

# 2. Логин юзера
echo -e "\n${YELLOW}--- Тест 2: Логин обычного пользователя ---${NC}"
USER_TOKEN=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' | jq -r '.access_token // empty')

if [ -z "$USER_TOKEN" ] || [ "$USER_TOKEN" == "null" ]; then
    echo -e "${RED}✗ Не удалось получить токен пользователя${NC}"
    exit 1
else
    echo -e "${GREEN}✓${NC} Токен пользователя получен"
fi

# 3. Получение информации о себе (админ)
echo -e "\n${YELLOW}--- Тест 3: GET /me (админ) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/me" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /me (админ)" "$STATUS" "200"

# 4. Получение информации о себе (юзер)
echo -e "\n${YELLOW}--- Тест 4: GET /me (юзер) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/me" \
  -H "Authorization: Bearer $USER_TOKEN")
print_result "GET /me (юзер)" "$STATUS" "200"

# 5. Получение списка задач (админ - должен видеть все)
echo -e "\n${YELLOW}--- Тест 5: GET /tasks (админ - все задачи) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /tasks (админ)" "$STATUS" "200"

# 6. Получение списка задач (юзер - только свои)
echo -e "\n${YELLOW}--- Тест 6: GET /tasks (юзер - только свои) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks" \
  -H "Authorization: Bearer $USER_TOKEN")
print_result "GET /tasks (юзер)" "$STATUS" "200"

# 7. Создание задачи (админ)
echo -e "\n${YELLOW}--- Тест 7: POST /tasks (админ создает задачу) ---${NC}"
ADMIN_TASK_STATUS=$(curl -s -o /tmp/admin_task_response.json -w "%{http_code}" -X POST "$BASE_URL/tasks" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Тестовая задача админа","description":"Описание","status":"pending"}')
ADMIN_TASK_ID=$(cat /tmp/admin_task_response.json | jq -r '.id // empty')
print_result "POST /tasks (админ)" "$ADMIN_TASK_STATUS" "201"

# 8. Создание задачи (юзер)
echo -e "\n${YELLOW}--- Тест 8: POST /tasks (юзер создает задачу) ---${NC}"
USER_TASK_STATUS=$(curl -s -o /tmp/user_task_response.json -w "%{http_code}" -X POST "$BASE_URL/tasks" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Тестовая задача юзера","description":"Описание","status":"pending"}')
USER_TASK_ID=$(cat /tmp/user_task_response.json | jq -r '.id // empty')
print_result "POST /tasks (юзер)" "$USER_TASK_STATUS" "201"

if [ -z "$ADMIN_TASK_ID" ] || [ -z "$USER_TASK_ID" ]; then
    echo -e "${RED}✗ Не удалось получить ID созданных задач${NC}"
    exit 1
fi

echo -e "  ID задачи админа: $ADMIN_TASK_ID"
echo -e "  ID задачи юзера: $USER_TASK_ID"

# 9. Получение задачи по ID (админ получает свою задачу)
echo -e "\n${YELLOW}--- Тест 9: GET /tasks/{id} (админ получает свою задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks/$ADMIN_TASK_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /tasks/$ADMIN_TASK_ID (админ)" "$STATUS" "200"

# 10. Получение задачи по ID (админ получает чужую задачу - должен иметь доступ)
echo -e "\n${YELLOW}--- Тест 10: GET /tasks/{id} (админ получает чужую задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks/$USER_TASK_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /tasks/$USER_TASK_ID (админ получает чужую)" "$STATUS" "200"

# 11. Получение задачи по ID (юзер получает свою задачу)
echo -e "\n${YELLOW}--- Тест 11: GET /tasks/{id} (юзер получает свою задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks/$USER_TASK_ID" \
  -H "Authorization: Bearer $USER_TOKEN")
print_result "GET /tasks/$USER_TASK_ID (юзер)" "$STATUS" "200"

# 12. Получение задачи по ID (юзер пытается получить чужую задачу - должен быть 403)
echo -e "\n${YELLOW}--- Тест 12: GET /tasks/{id} (юзер пытается получить чужую задачу - должен быть 403) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks/$ADMIN_TASK_ID" \
  -H "Authorization: Bearer $USER_TOKEN")
print_result "GET /tasks/$ADMIN_TASK_ID (юзер получает чужую)" "$STATUS" "403"

# 13. Обновление задачи (админ обновляет свою задачу)
echo -e "\n${YELLOW}--- Тест 13: PUT /tasks/{id} (админ обновляет свою задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$BASE_URL/tasks/$ADMIN_TASK_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Обновленная задача админа","status":"in_progress"}')
print_result "PUT /tasks/$ADMIN_TASK_ID (админ)" "$STATUS" "200"

# 14. Обновление задачи (админ обновляет чужую задачу)
echo -e "\n${YELLOW}--- Тест 14: PUT /tasks/{id} (админ обновляет чужую задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$BASE_URL/tasks/$USER_TASK_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Обновленная задача юзера админом","status":"completed"}')
print_result "PUT /tasks/$USER_TASK_ID (админ обновляет чужую)" "$STATUS" "200"

# 15. Обновление задачи (юзер обновляет свою задачу)
echo -e "\n${YELLOW}--- Тест 15: PUT /tasks/{id} (юзер обновляет свою задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$BASE_URL/tasks/$USER_TASK_ID" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Обновленная задача юзера","status":"in_progress"}')
print_result "PUT /tasks/$USER_TASK_ID (юзер)" "$STATUS" "200"

# 16. Обновление задачи (юзер пытается обновить чужую задачу - должен быть 403)
echo -e "\n${YELLOW}--- Тест 16: PUT /tasks/{id} (юзер пытается обновить чужую задачу - должен быть 403) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X PUT "$BASE_URL/tasks/$ADMIN_TASK_ID" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Попытка обновить чужую задачу","status":"completed"}')
print_result "PUT /tasks/$ADMIN_TASK_ID (юзер обновляет чужую)" "$STATUS" "403"

# 17. Удаление задачи (админ удаляет чужую задачу)
echo -e "\n${YELLOW}--- Тест 17: DELETE /tasks/{id} (админ удаляет чужую задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/tasks/$USER_TASK_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "DELETE /tasks/$USER_TASK_ID (админ удаляет чужую)" "$STATUS" "200"

# Создаем новую задачу юзера для следующего теста
curl -s -o /tmp/user_task_response2.json -X POST "$BASE_URL/tasks" \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Задача юзера для удаления","description":"Описание","status":"pending"}' > /dev/null
USER_TASK_ID2=$(cat /tmp/user_task_response2.json | jq -r '.id // empty')

# 18. Удаление задачи (юзер удаляет свою задачу)
echo -e "\n${YELLOW}--- Тест 18: DELETE /tasks/{id} (юзер удаляет свою задачу) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/tasks/$USER_TASK_ID2" \
  -H "Authorization: Bearer $USER_TOKEN")
print_result "DELETE /tasks/$USER_TASK_ID2 (юзер удаляет свою)" "$STATUS" "200"

# 19. Удаление задачи (юзер пытается удалить чужую задачу - должен быть 403)
echo -e "\n${YELLOW}--- Тест 19: DELETE /tasks/{id} (юзер пытается удалить чужую задачу - должен быть 403) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X DELETE "$BASE_URL/tasks/$ADMIN_TASK_ID" \
  -H "Authorization: Bearer $USER_TOKEN")
print_result "DELETE /tasks/$ADMIN_TASK_ID (юзер удаляет чужую)" "$STATUS" "403"

# 20. Фильтрация задач по статусу
echo -e "\n${YELLOW}--- Тест 20: GET /tasks?status=completed (фильтрация) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks?status=completed" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /tasks?status=completed" "$STATUS" "200"

# 21. Пагинация
echo -e "\n${YELLOW}--- Тест 21: GET /tasks?page=1&per_page=5 (пагинация) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks?page=1&per_page=5" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /tasks?page=1&per_page=5" "$STATUS" "200"

# 22. Валидация - создание задачи без обязательных полей
echo -e "\n${YELLOW}--- Тест 22: POST /tasks (без title - должен быть 422) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/tasks" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"description":"Без title"}')
print_result "POST /tasks (без title)" "$STATUS" "422"

# 23. Валидация - неверный статус
echo -e "\n${YELLOW}--- Тест 23: POST /tasks (неверный статус - должен быть 422) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/tasks" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Задача","status":"invalid_status"}')
print_result "POST /tasks (неверный статус)" "$STATUS" "422"

# 24. Несуществующая задача
echo -e "\n${YELLOW}--- Тест 24: GET /tasks/99999 (несуществующая задача - должен быть 404) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks/99999" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /tasks/99999 (несуществующая)" "$STATUS" "404"

# 25. Запрос без токена
echo -e "\n${YELLOW}--- Тест 25: GET /tasks (без токена - должен быть 401) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks")
print_result "GET /tasks (без токена)" "$STATUS" "401"

# 26. Запрос с неверным токеном
echo -e "\n${YELLOW}--- Тест 26: GET /tasks (с неверным токеном - должен быть 401) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/tasks" \
  -H "Authorization: Bearer invalid_token_12345")
print_result "GET /tasks (неверный токен)" "$STATUS" "401"

# 27. Logout
echo -e "\n${YELLOW}--- Тест 27: POST /logout (выход) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE_URL/logout" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "POST /logout" "$STATUS" "200"

# 28. Запрос после logout (токен должен быть недействителен)
echo -e "\n${YELLOW}--- Тест 28: GET /me (после logout - должен быть 401) ---${NC}"
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/me" \
  -H "Authorization: Bearer $ADMIN_TOKEN")
print_result "GET /me (после logout)" "$STATUS" "401"

# Очистка - удаление тестовой задачи админа
echo -e "\n${YELLOW}--- Очистка: удаление тестовой задачи ---${NC}"
curl -s -X DELETE "$BASE_URL/tasks/$ADMIN_TASK_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" > /dev/null

# Очистка временных файлов
rm -f /tmp/admin_task_response.json /tmp/user_task_response.json /tmp/user_task_response2.json

echo -e "\n${GREEN}=== Тестирование завершено ===${NC}"

