# Запустить проект
up:
	docker compose up -d

# Остановить проект
down:
	docker compose down

# Перезапустить
restart: down up

# Войти в контейнер PHP
sh:
	docker compose exec php sh

# Установить зависимости
composer:
	docker compose exec php composer install --ignore-platform-reqs

# Обновить зависимости
update:
	docker compose exec php composer update --ignore-platform-reqs

# Миграции
migrate:
	docker compose exec php php yii migrate/up --interactive=0

# Создать миграцию
create-migration:
	docker compose exec php php yii migrate/create

# Исправить права
perms:
	docker compose exec php sh -c "chown -R www-data:www-data runtime web/assets && chmod -R 755 runtime web/assets"

# Логи
logs:
	docker compose logs -f

# Логи только ошибок PHP
logs-php:
	docker compose logs -f php

# Войти в БД
db:
	docker compose exec postgres psql -U boardgame_user -d boardgame