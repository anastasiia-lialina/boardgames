# Переменная по умолчанию для помощи
.DEFAULT_GOAL := help

# Список всех "ложных" целей (не файлов)
.PHONY: help up down restart sh composer update migrate migrate-create migrate-down-all migrate-down perms logs logs-php db

help: ## Помощь
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Запустить проект в фоне
	docker compose up -d

down: ## Остановить проект
	docker compose down

restart: down up ## Перезапустить

sh: ## Войти в контейнер PHP
	docker compose exec php sh

composer-install: ## Установить зависимости
	docker compose exec php composer install --ignore-platform-reqs

composer-require: ##   Требовать зависимость
	docker compose exec php composer require --prefer-dist ${lib}

composer-remove: ##   Удалить зависимость
	docker compose exec php composer remove ${lib}

composer-update: ## Обновить зависимости
	docker compose exec php composer update --ignore-platform-reqs

migrate: ## Применить миграции
	docker compose exec php php yii migrate/up --interactive=0

migrate-create: ## Создать миграцию
	docker compose exec php php yii migrate/create $(name)

migrate-down-all: ## Откат всех миграций
	docker compose exec php php yii migrate/down all

migrate-down: ## Откат n последних миграций
	docker compose exec php php yii migrate/down ${n}

perms: ## Исправить права
	docker compose exec php sh -c "chown -R www-data:www-data runtime web/assets && chmod -R 755 runtime web/assets"

logs: ## Логи
	docker compose logs -f

logs-php: ## Логи только ошибок PHP
	docker compose logs -f php

db: ## Войти в БД
	docker compose exec postgres psql -U boardgame_user -d boardgame

cache-clear: ##  Очистить кэш
	docker compose exec php php yii cache/flush-all

game-session-update: ##  Обновить статусы игровых сессий
	docker compose exec php php yii session/update-status

game-session-check: ##  Проверить статусы игровых сессий
	docker compose exec php php yii session/check-stale
