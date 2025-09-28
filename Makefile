# 🎵 Music Practice Tracker - Makefile
# Удобные команды для разработки и управления проектом

.PHONY: help install dev build test clean docker-start docker-stop docker-restart logs

# Цвета для вывода
GREEN=\033[0;32m
YELLOW=\033[1;33m
RED=\033[0;31m
NC=\033[0m # No Color

# Помощь по командам
help: ## Показать справку по командам
	@echo "$(GREEN)🎵 Music Practice Tracker - Доступные команды:$(NC)"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  $(YELLOW)%-20s$(NC) %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""

# Установка и настройка
install: ## Полная установка проекта
	@echo "$(GREEN)📦 Установка зависимостей...$(NC)"
	composer install
	npm install
	@echo "$(GREEN)🔧 Настройка окружения...$(NC)"
	cp .env.example .env
	php artisan key:generate
	@echo "$(GREEN)🗄️ Настройка базы данных...$(NC)"
	php artisan migrate
	@echo "$(GREEN)🏗️ Сборка фронтенда...$(NC)"
	npm run build
	@echo "$(GREEN)✅ Установка завершена!$(NC)"

install-dev: ## Установка для разработки
	@echo "$(GREEN)📦 Установка зависимостей для разработки...$(NC)"
	composer install
	npm install
	cp .env.example .env
	php artisan key:generate
	php artisan migrate
	npm run dev
	@echo "$(GREEN)✅ Установка для разработки завершена!$(NC)"

# Разработка
dev: ## Запуск сервера разработки
	@echo "$(GREEN)🚀 Запуск сервера разработки...$(NC)"
	php artisan serve

dev-watch: ## Запуск с отслеживанием изменений
	@echo "$(GREEN)👀 Запуск с отслеживанием изменений...$(NC)"
	npm run dev

build: ## Сборка фронтенда для продакшена
	@echo "$(GREEN)🏗️ Сборка фронтенда...$(NC)"
	npm run build

build-watch: ## Сборка с отслеживанием изменений
	@echo "$(GREEN)👀 Сборка с отслеживанием изменений...$(NC)"
	npm run build -- --watch

# База данных
migrate: ## Запуск миграций
	@echo "$(GREEN)🗄️ Запуск миграций...$(NC)"
	php artisan migrate

migrate-fresh: ## Пересоздание базы данных
	@echo "$(GREEN)🗄️ Пересоздание базы данных...$(NC)"
	php artisan migrate:fresh

migrate-seed: ## Миграции с заполнением данными
	@echo "$(GREEN)🗄️ Миграции с заполнением данными...$(NC)"
	php artisan migrate:fresh --seed

# Тестирование
test: ## Запуск всех тестов
	@echo "$(GREEN)🧪 Запуск тестов...$(NC)"
	php artisan test
	npm run test

test-php: ## Запуск PHP тестов
	@echo "$(GREEN)🧪 Запуск PHP тестов...$(NC)"
	php artisan test

test-js: ## Запуск JavaScript тестов
	@echo "$(GREEN)🧪 Запуск JavaScript тестов...$(NC)"
	npm run test

# Очистка
clean: ## Очистка кэша и временных файлов
	@echo "$(GREEN)🧹 Очистка кэша...$(NC)"
	php artisan cache:clear
	php artisan config:clear
	php artisan route:clear
	php artisan view:clear
	npm run clean 2>/dev/null || true
	@echo "$(GREEN)✅ Очистка завершена!$(NC)"

clean-all: clean ## Полная очистка (включая node_modules и vendor)
	@echo "$(GREEN)🧹 Полная очистка...$(NC)"
	rm -rf node_modules
	rm -rf vendor
	rm -rf public/build
	@echo "$(GREEN)✅ Полная очистка завершена!$(NC)"

# Docker команды
docker-up: ## Запуск всех Docker контейнеров
	@echo "$(GREEN)🐳 Запуск всех Docker контейнеров...$(NC)"
	docker-compose up -d

docker-down: ## Остановка всех Docker контейнеров
	@echo "$(GREEN)🐳 Остановка всех Docker контейнеров...$(NC)"
	docker-compose down

docker-restart: ## Перезапуск Docker контейнеров
	@echo "$(GREEN)🐳 Перезапуск Docker контейнеров...$(NC)"
	docker-compose restart

docker-build: ## Сборка Docker образов
	@echo "$(GREEN)🐳 Сборка Docker образов...$(NC)"
	docker-compose build

docker-build-no-cache: ## Сборка Docker образов без кэша
	@echo "$(GREEN)🐳 Сборка Docker образов без кэша...$(NC)"
	docker-compose build --no-cache

docker-logs: ## Просмотр логов всех контейнеров
	@echo "$(GREEN)📋 Просмотр логов всех контейнеров...$(NC)"
	docker-compose logs -f

docker-logs-app: ## Просмотр логов приложения
	@echo "$(GREEN)📋 Просмотр логов приложения...$(NC)"
	docker-compose logs -f app

docker-logs-nginx: ## Просмотр логов Nginx
	@echo "$(GREEN)📋 Просмотр логов Nginx...$(NC)"
	docker-compose logs -f nginx

docker-logs-mysql: ## Просмотр логов MySQL
	@echo "$(GREEN)📋 Просмотр логов MySQL...$(NC)"
	docker-compose logs -f mysql

docker-shell: ## Подключение к контейнеру приложения
	@echo "$(GREEN)🐚 Подключение к контейнеру приложения...$(NC)"
	docker-compose exec app bash

docker-shell-mysql: ## Подключение к MySQL
	@echo "$(GREEN)🐚 Подключение к MySQL...$(NC)"
	docker-compose exec mysql mysql -u music_tracker -p music_tracker

docker-clean: ## Очистка Docker (контейнеры, образы, тома)
	@echo "$(GREEN)🧹 Очистка Docker...$(NC)"
	docker-compose down -v --remove-orphans
	docker system prune -f

docker-setup: ## Полная настройка Docker окружения
	@echo "$(GREEN)🚀 Полная настройка Docker окружения...$(NC)"
	cp .env.docker .env
	docker-compose build
	docker-compose up -d
	@echo "$(GREEN)⏳ Ожидание запуска сервисов...$(NC)"
	sleep 30
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate
	docker-compose exec app php artisan storage:link
	@echo "$(GREEN)✅ Docker окружение готово!$(NC)"
	@echo "$(GREEN)🌐 Приложение доступно по адресу: http://localhost$(NC)"
	@echo "$(GREEN)🗄️ MinIO консоль: http://localhost:9001 (minioadmin/minioadmin123)$(NC)"

# Логи и отладка
logs: ## Просмотр логов Laravel
	@echo "$(GREEN)📋 Просмотр логов Laravel...$(NC)"
	tail -f storage/logs/laravel.log

logs-clear: ## Очистка логов
	@echo "$(GREEN)🧹 Очистка логов...$(NC)"
	rm -f storage/logs/laravel.log
	touch storage/logs/laravel.log
	@echo "$(GREEN)✅ Логи очищены!$(NC)"

# Утилиты
tinker: ## Запуск Laravel Tinker
	@echo "$(GREEN)🔧 Запуск Laravel Tinker...$(NC)"
	php artisan tinker

route-list: ## Список маршрутов
	@echo "$(GREEN)🛣️ Список маршрутов...$(NC)"
	php artisan route:list

config-cache: ## Кэширование конфигурации
	@echo "$(GREEN)⚡ Кэширование конфигурации...$(NC)"
	php artisan config:cache

route-cache: ## Кэширование маршрутов
	@echo "$(GREEN)⚡ Кэширование маршрутов...$(NC)"
	php artisan route:cache

view-cache: ## Кэширование представлений
	@echo "$(GREEN)⚡ Кэширование представлений...$(NC)"
	php artisan view:cache

# Проверки
check: ## Проверка кода
	@echo "$(GREEN)🔍 Проверка кода...$(NC)"
	composer validate
	npm audit
	@echo "$(GREEN)✅ Проверка завершена!$(NC)"

lint: ## Линтинг кода
	@echo "$(GREEN)🔍 Линтинг кода...$(NC)"
	./vendor/bin/pint --test
	npm run lint

lint-fix: ## Исправление ошибок линтера
	@echo "$(GREEN)🔧 Исправление ошибок линтера...$(NC)"
	./vendor/bin/pint
	npm run lint:fix

# Продакшен
prod: ## Подготовка к продакшену
	@echo "$(GREEN)🚀 Подготовка к продакшену...$(NC)"
	composer install --optimize-autoloader --no-dev
	npm run build
	php artisan config:cache
	php artisan route:cache
	php artisan view:cache
	@echo "$(GREEN)✅ Готово к продакшену!$(NC)"

# Резервное копирование
backup-db: ## Резервное копирование базы данных
	@echo "$(GREEN)💾 Резервное копирование базы данных...$(NC)"
	php artisan backup:run

# Обновление
update: ## Обновление зависимостей
	@echo "$(GREEN)🔄 Обновление зависимостей...$(NC)"
	composer update
	npm update
	@echo "$(GREEN)✅ Обновление завершено!$(NC)"

# Статистика
stats: ## Статистика проекта
	@echo "$(GREEN)📊 Статистика проекта:$(NC)"
	@echo "PHP файлы: $$(find . -name '*.php' | wc -l)"
	@echo "JavaScript файлы: $$(find . -name '*.js' | wc -l)"
	@echo "Vue файлы: $$(find . -name '*.vue' | wc -l)"
	@echo "Размер проекта: $$(du -sh . | cut -f1)"

# По умолчанию показываем справку
.DEFAULT_GOAL := help