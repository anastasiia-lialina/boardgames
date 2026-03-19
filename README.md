# Boardgames

## Описание

Проект настольных игр с системой отзывов, сессий и подписок на базе Yii2 Framework.

## Технологии

### Backend

- **PHP 8.2** - Основной язык
- **Yii2 Framework** - MVC фреймворк
- **PostgreSQL 14** - СУБД
- **Redis 7** - Кэширование
- **RabbitMQ 3** - Очереди сообщений
- **Nginx** - Веб-сервер

### Frontend

- **Bootstrap 5** - UI фреймворк
- **jQuery** - JavaScript библиотека

### Инструменты

- **Docker & Docker Compose** - Контейнеризация
- **Composer** - Управление зависимостями
- **Codeception** - Тестирование
- **Make** - Автоматизация

# Архитектура

### MVC структура с сервисным слоем

```
/
├── controllers/          # Обработка HTTP запросов
├── models/               # Модели данных
│   ├── forms/           # Формы валидации
│   ├── game/            # Модели игр
│   ├── user/            # Модели пользователей
│   └── search/          # Модели поиска
├── services/             # Бизнес-логика
├── views/               # Шаблоны
```

### Основные возможности

- Каталог игр с фильтрацией и поиском
- Система отзывов с модерацией
- Организация игровых сессий
- Подписки на игры
- Управление пользователями и ролями

### Установка и запуск

#### Через Docker (рекомендуется)

```bash
# Клонирование репозитория
git clone <repository-url>
cd boardgames

# Копирование конфигурации
cp .env.example .env

# Запуск контейнеров
make up

# Установка зависимостей
make composer-install

# Выполнение миграций
make migrate
```

#### Вручную

```bash
# Установка зависимостей
composer install

# Настройка окружения
cp .env.example .env
# Отредактируйте .env файл с вашими настройками

# Миграции базы данных
php yii migrate

# Запуск сервера разработки
php yii serve
```

### Доступные команды (Make)

```bash
# Управление контейнерами
make up              # Запустить контейнеры в фоне
make down            # Остановить контейнеры
make restart         # Перезапустить проект
make sh              # Войти в PHP контейнер

# Управление зависимостями
make composer-install # Установить зависимости
make composer-require lib=package  # Добавить пакет
make composer-remove lib=package    # Удалить пакет
make composer-update  # Обновить зависимости

# Миграции базы данных
make migrate                    # Применить миграции
make migrate-create name=migration_name  # Создать миграцию
make migrate-down-all          # Откатить все миграции
make migrate-down n=2          # Откатить последние 2 миграции

# Утилиты
make perms           # Исправить права доступа
make logs            # Просмотр всех логов
make logs-php        # Логи только PHP
make db              # Подключиться к PostgreSQL
make cache-clear     # Очистить кэш

# Специфичные команды проекта
make game-session-update  # Обновить статусы игровых сессий
make game-session-check    # Проверить устаревшие сессии

# Помощь
make help             # Показать все доступные команды
```

### Структура проекта

```
/
├── controllers/          # Контроллеры
├── models/             # Модели данных
│   ├── game/          # Модели игр
│   ├── user/          # Модели пользователей
│   └── forms/         # Формы валидации
├── services/           # Сервисный слой
├── views/              # Шаблоны представлений
├── messages/           # Файлы локализации
├── migrations/         # Миграции БД
├── config/            # Конфигурация приложения
└── docker/            # Docker конфигурации
```

### Код стиль

Проект использует PHP CS Fixer для поддержания единого стиля кода:

```bash
# Проверка стиля
make check-style

# Исправление стиля
make fix
```

- **Разработчик**: Анастасия Бычкина
- **Email**: lyalinaav@gmail.com