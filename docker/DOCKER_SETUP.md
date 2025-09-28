# 🐳 Docker Setup для Music Practice Tracker

Полная Docker-конфигурация для запуска всего стека приложения.

## 🏗️ Архитектура

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Nginx       │    │   PHP-FPM       │    │     MySQL       │
│   (Port 80)     │◄──►│   (Port 9000)   │◄──►│   (Port 3306)   │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Redis       │    │     MinIO       │    │     Node.js     │
│   (Port 6379)   │    │ (Port 9000/9001)│    │   (Build only)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## 🚀 Быстрый старт

### 1. Полная настройка (рекомендуется)

```bash
# Клонируйте репозиторий
git clone <repository-url>
cd music-practice-tracker

# Полная настройка Docker окружения
make docker-setup
```

Эта команда:
- Скопирует `.env.docker` в `.env`
- Соберет все Docker образы
- Запустит все контейнеры
- Настроит Laravel (ключ, миграции, storage link)
- Покажет ссылки для доступа

### 2. Ручная настройка

```bash
# 1. Настройка окружения
cp .env.docker .env

# 2. Сборка образов
docker-compose build

# 3. Запуск контейнеров
docker-compose up -d

# 4. Ожидание запуска сервисов
sleep 30

# 5. Настройка Laravel
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan storage:link
```

## 🌐 Доступ к сервисам

После запуска приложение будет доступно по следующим адресам:

- **🌐 Приложение**: http://localhost
- **🗄️ MinIO Console**: http://localhost:9001
  - Логин: `minioadmin`
  - Пароль: `minioadmin123`
- **📊 MySQL**: localhost:3306
  - База: `music_tracker`
  - Пользователь: `music_tracker`
  - Пароль: `music_tracker_password`
- **🔴 Redis**: localhost:6379

## 🛠️ Управление контейнерами

### Основные команды

```bash
# Запуск всех контейнеров
make docker-up

# Остановка всех контейнеров
make docker-down

# Перезапуск контейнеров
make docker-restart

# Сборка образов
make docker-build

# Сборка без кэша
make docker-build-no-cache
```

### Просмотр логов

```bash
# Все логи
make docker-logs

# Логи приложения
make docker-logs-app

# Логи Nginx
make docker-logs-nginx

# Логи MySQL
make docker-logs-mysql
```

### Подключение к контейнерам

```bash
# Подключение к приложению
make docker-shell

# Подключение к MySQL
make docker-shell-mysql
```

## 🔧 Конфигурация сервисов

### PHP-FPM (app)
- **Образ**: Custom PHP 8.2-FPM
- **Расширения**: MySQL, Redis, GD, ZIP, OPcache
- **Конфигурация**: `docker/php/local.ini`
- **Рабочая директория**: `/var/www/html`

### Nginx
- **Образ**: nginx:alpine
- **Конфигурация**: `docker/nginx/`
- **Порты**: 80, 443
- **Статические файлы**: кэширование, сжатие

### MySQL
- **Образ**: mysql:8.0
- **База данных**: `music_tracker`
- **Инициализация**: `docker/mysql/init/`
- **Том**: `mysql_data`

### Redis
- **Образ**: redis:7-alpine
- **Конфигурация**: с персистентностью
- **Том**: `redis_data`

### MinIO
- **Образ**: minio/minio:latest
- **Консоль**: порт 9001
- **API**: порт 9000
- **Автоматическая настройка**: bucket `music-tracker-notes`

## 📁 Структура Docker файлов

```
docker/
├── mysql/
│   └── init/
│       └── 01-init.sql          # Инициализация БД
├── nginx/
│   ├── default.conf             # Конфигурация сайта
│   └── nginx.conf               # Основная конфигурация
└── php/
    └── local.ini                # Конфигурация PHP
```

## 🧹 Очистка

```bash
# Очистка контейнеров, образов и томов
make docker-clean

# Или вручную
docker-compose down -v --remove-orphans
docker system prune -f
```

## 🐛 Отладка

### Проверка статуса контейнеров

```bash
docker-compose ps
```

### Проверка логов

```bash
# Конкретный сервис
docker-compose logs app
docker-compose logs nginx
docker-compose logs mysql

# Последние 100 строк
docker-compose logs --tail=100 app
```

### Подключение к контейнеру

```bash
# Bash в контейнере приложения
docker-compose exec app bash

# MySQL CLI
docker-compose exec mysql mysql -u music_tracker -p music_tracker
```

### Проверка сетей

```bash
docker network ls
docker network inspect music-practice-tracker_music-tracker-network
```

## 🔒 Безопасность

- Все сервисы работают в изолированной сети
- Nginx настроен с security headers
- PHP с ограничениями на загрузку файлов
- MySQL с отдельным пользователем (не root)

## 📊 Мониторинг

### Health checks

```bash
# Проверка здоровья контейнеров
docker-compose ps

# Проверка health check приложения
curl http://localhost/health
```

### Ресурсы

```bash
# Использование ресурсов
docker stats
```

## 🚀 Продакшен

Для продакшена рекомендуется:

1. Использовать отдельный `.env.production`
2. Настроить SSL сертификаты
3. Использовать внешние тома для данных
4. Настроить мониторинг и логирование
5. Использовать Docker Swarm или Kubernetes

## ❓ Часто задаваемые вопросы

### Q: Контейнеры не запускаются
A: Проверьте логи: `make docker-logs` и убедитесь, что порты свободны.

### Q: База данных не подключается
A: Убедитесь, что MySQL контейнер запущен: `docker-compose ps`

### Q: Статические файлы не загружаются
A: Проверьте, что выполнен `php artisan storage:link`

### Q: MinIO недоступен
A: Проверьте, что MinIO контейнер запущен и bucket создан.

---

**🎵 Готово к разработке!** Ваше Docker окружение настроено и готово к работе.