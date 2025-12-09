# Тестовое задание

## Запуск (Linux)

- Скопировть файл `.env.example` под именем `.env`
  
  ```bash
  cp .env.example .env
  ```

- Сборка контейнеров:

  ```bash
  docker compose build
  ```

- Установка зависимостей:

  ```bash
  docker compose run --rm php composer install 
  ```

- Запуск проекта:

  ```bash
  docker compose up -d
  ```

- Приминение миграций:

  ```bash
  docker compose exec -u www-data php yii migrate/up --interactive 0
  ```

- Проект доступен по `http://localhost:8000/`

- Для просмотра логов очереди отправки смс:

  ```bash
  docker compose logs -f queue
  ```
