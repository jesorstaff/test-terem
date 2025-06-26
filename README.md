# Система бронирования отелей

## Установка и настройка

### Развертывание в Docker

1. **Клонируйте репозиторий:**

    ```bash
    git clone <repository-url>
    cd <repository-name>
    ```

2. **Настройте базу данных в `.env`: для этого нужно создать файл в корне проекта и добавить собержимое**

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=pgsql
    DB_PORT=5432
    DB_DATABASE=db
    DB_USERNAME=user
    DB_PASSWORD=password
    ```

3. **Настройте `.env`: для этого нужно создать файл и добавить собержимое в папку docker_test/.env**

    ```env
    PROJECT_NAME=docker
    NGINX_PORT=92

    DB_CONNECTION=pgsql
    DB_HOST=pgsql
    DB_PORT=5427
    DB_DATABASE=db
    DB_USERNAME=user
    DB_PASSWORD=password
    ```

4. **Перейдите в папку с Docker-конфигурацией:**

    ```bash
    cd docker_test
    ```

5. **Запустите контейнеры:**

    ```bash
    docker-compose up -d
    ```

6. **Установите зависимости внутри контейнера:**

    ```bash
    docker-compose exec php-fpm bash
    ```

    ```bash
    composer install
    ```

7. **Выполните миграции:**

    ```bash
    docker-compose exec php-fpm bash
    ```

    ```bash
    php artisan migrate
    ```

8. **Заполните базу тестовыми данными:**

    ```bash
    docker-compose exec php-fpm bash
    ```

    ```bash
    php artisan db:seed
    ```

9. **Приложение будет доступно по адресу:**
    ```
    http://localhost:92
    ```

## API Endpoints

### Аутентификация

#### POST /api/login

Вход в систему и получение токена доступа.

**Запрос:**

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Ответ:**

```json
{
    "token": "2|EjvbFJiaYwDA3xEZyeKwL4GeAn2rhmiTb4c3AoZy3ea087b7"
}
```

### Номера

#### GET /api/rooms

Получение списка всех номеров (публичный доступ).

**Ответ:**

```json
[
    {
        "id": 1,
        "name": "Люкс",
        "description": "Просторный номер с видом на море",
        "created_at": "2025-06-24T15:49:56.000000Z",
        "updated_at": "2025-06-24T15:49:56.000000Z"
    }
]
```

### Бронирование

#### POST /api/bookings

Создание нового бронирования (требует аутентификации).

**Заголовки:**

```
Authorization: Bearer 2|EjvbFJiaYwDA3xEZyeKwL4GeAn2rhmiTb4c3AoZy3ea087b7
Content-Type: application/json
Accept: application/json
```

**Запрос:**

```json
{
    "room_id": 1,
    "start_date": "2024-01-01",
    "end_date": "2024-01-03"
}
```

**Ответ:**

```json
{
    "message": "Номер успешно зарегистирован"
}
```

### Проверка работоспособности API

1. **Проверка аутентификации:**

    ```bash
    curl -X POST http://localhost:92/api/login \
      -H "Content-Type: application/json" \
      -d '{"email":"user@example.com","password":"password"}'
    ```

2. **Получение списка номеров:**

    ```bash
    curl -X GET http://localhost:92/api/rooms
    ```

3. **Создание бронирования:**
    ```bash
    curl -X POST http://localhost:92/api/bookings \
      -H "Authorization: Bearer YOUR_TOKEN" \
      -H "Content-Type: application/json" \
      -d '{"room_id":1,"start_date":"2025-01-01","end_date":"2026-01-03"}'
    ```

### Проверка логов

Email-уведомления логируются в файл:

```bash
tail -f storage/logs/laravel.log
```
