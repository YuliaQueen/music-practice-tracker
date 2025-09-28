# 🐳 Docker Configuration

Эта папка содержит всю конфигурацию Docker для проекта Music Practice Tracker.

## 📁 Структура

```
docker/
├── README.md              # Этот файл
├── DOCKER_SETUP.md        # Подробная документация по настройке Docker
├── Dockerfile             # Docker образ для PHP приложения
├── .dockerignore          # Игнорируемые файлы при сборке Docker
├── docker-start.sh        # Скрипт запуска Docker (устарел, используйте Makefile)
├── docker-stop.sh         # Скрипт остановки Docker (устарел, используйте Makefile)
├── mysql/
│   └── init/
│       └── 01-init.sql    # Инициализация MySQL базы данных
├── nginx/
│   ├── default.conf       # Конфигурация Nginx для Laravel
│   └── nginx.conf         # Основная конфигурация Nginx
└── php/
    └── local.ini          # Конфигурация PHP для Docker
```

## 🚀 Быстрый старт

Для запуска Docker окружения используйте команды из корневого Makefile:

```bash
# Полная настройка Docker (один раз)
make docker-setup

# Запуск всех контейнеров
make docker-up

# Остановка всех контейнеров
make docker-down

# Просмотр логов
make docker-logs
```

## 📚 Документация

- **[DOCKER_SETUP.md](./DOCKER_SETUP.md)** - Подробная документация по настройке и использованию Docker
- **[README.md](../README.md)** - Общая документация проекта

## 🔧 Сервисы

- **PHP 8.3-FPM** - Основное приложение Laravel
- **Nginx** - Веб-сервер (порт 80)
- **MySQL 8.0** - База данных (порт 3306)
- **Redis** - Кэш и сессии (порт 6379)
- **MinIO** - Файловое хранилище (порты 9000/9001)

## 🌐 Доступ к сервисам

- **Приложение**: http://localhost
- **MinIO Console**: http://localhost:9001 (minioadmin/minioadmin123)
- **MySQL**: localhost:3306 (music_tracker/music_tracker_password)

> **Примечание**: Пароли по умолчанию используются только для development. Для production измените переменные окружения.
- **Redis**: localhost:6379