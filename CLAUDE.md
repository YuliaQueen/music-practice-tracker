# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 🎵 Music Practice Tracker

Веб-приложение для музыкантов: организация практических сессий, отслеживание прогресса, аудио записи, метроном, PWA-поддержка.

**Stack**: Laravel 12 + Vue 3 (TypeScript) + Inertia.js + Tailwind CSS + MinIO

---

## Development Commands

### Основные команды (Make)
```bash
make help              # Список всех команд
make install           # Полная установка
make dev               # Запуск dev-сервера (php artisan serve)
make dev-watch         # Vite с hot reload (npm run dev)
make build             # Продакшн-сборка фронтенда

# Тестирование
make test              # Все тесты
make test-php          # PHP тесты (php artisan test)
make test-js           # JS тесты

# База данных
make migrate           # Миграции
make migrate-fresh     # Пересоздание БД
make migrate-seed      # Миграции + сидеры

# Docker
make docker-setup      # Полная настройка Docker окружения
make docker-up         # Запуск контейнеров
make docker-down       # Остановка
make docker-shell      # Shell в app-контейнере

# Утилиты
make clean             # Очистка кэша Laravel
make logs              # tail -f storage/logs/laravel.log
make tinker            # Laravel Tinker
make lint              # Проверка кода
make lint-fix          # Автофикс стиля
```

### Прямые команды
```bash
# Backend
composer install
php artisan migrate
php artisan test --filter SessionTest  # Один тест

# Frontend
npm install
npm run dev        # Vite dev server
npm run build      # Production build
npm run lint       # ESLint
```

---

## Архитектура

### Domain-Driven Design (DDD)

Проект использует доменную архитектуру:

```
app/Domains/{Domain}/
├── Models/          # Eloquent модели
├── Repositories/    # Репозитории (интерфейс + реализация)
├── Services/        # Доменная бизнес-логика
├── Contracts/       # Интерфейсы
└── Providers/       # ServiceProvider для регистрации
```

**Существующие домены:**
- **User** - пользователи, профили
- **Planning** - сессии, упражнения, шаблоны, блоки сессий
- **Goals** - цели пользователя
- **Journal** - заметки пользователя
- **Recording** - аудио записи исполнений
- **Shared** - общие трейты (BelongsToUser, HasStatus, HasMetadata)

### Ключевые паттерны

**1. Repository Pattern**
```php
// Интерфейс
interface SessionRepositoryInterface {
    public function getForUser(int $userId, int $perPage = 10, array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?Session;
}

// Реализация в app/Domains/Planning/Repositories/
// Регистрация в PlanningServiceProvider
```

**2. Service Layer**
```php
// app/Services/SessionService.php
public function createSession(User $user, CreateSessionDTO $dto): array
{
    try {
        DB::beginTransaction();
        // Логика
        DB::commit();
        return ['success' => true, 'session' => $session, 'message' => '...'];
    } catch (\Throwable $e) {
        if (DB::transactionLevel() > 0) {
            try { DB::rollBack(); }
            catch (\Throwable $re) { Log::error('Rollback error', ['error' => $re->getMessage()]); }
        }
        Log::error('Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return ['success' => false, 'message' => 'Ошибка...'];
    }
}
```

**3. DTOs** (app/DTOs/)
```php
// app/DTOs/Sessions/CreateSessionDTO.php
class CreateSessionDTO {
    public static function fromRequest(Request $request): self { }
    public function toArray(): array { }
}
```

**4. Form Requests** (app/Http/Requests/)
```php
class StoreSessionRequest extends FormRequest {
    public function rules(): array { }
    public function attributes(): array { } // Русские названия полей
}
```

**5. Тонкие контроллеры**
```php
public function store(StoreSessionRequest $request): RedirectResponse
{
    $dto = CreateSessionDTO::fromRequest($request);
    $result = $this->sessionService->createSession(auth()->user(), $dto);

    if (!$result['success']) {
        return back()->withInput()->with('error', $result['message']);
    }

    return redirect()->route('sessions.show', $result['session'])
        ->with('success', $result['message']);
}
```

### Frontend Architecture (Vue 3 + TypeScript)

```
resources/js/
├── Components/           # Переиспользуемые компоненты
│   ├── Session/         # SessionTimer, SessionControlBar, SessionBlocksList
│   ├── Audio/           # AudioRecorder, AudioRecordingsList
│   └── Metronome/       # CompactMetronome, MetronomeWidget
├── Pages/               # Inertia страницы
│   ├── Sessions/        # Index, Create, Show
│   ├── Exercises/
│   ├── Goals/
│   └── Statistics/
├── Layouts/             # AuthenticatedLayout, GuestLayout
├── composables/         # useTimerSounds, useMetronome, useI18n, useTheme
├── locales/            # ru/, en/ - переводы (vue-i18n)
├── types/              # models.ts, global.d.ts
└── utils/              # exerciseHelpers, timeHelpers, statusHelpers
```

**Важные composables:**
- `useTimerSounds()` - звуки таймера (start/pause/complete/warning)
- `useMetronome()` - Web Audio API метроном
- `useI18n()` - интернационализация (русский/английский)
- `useTheme()` - темная/светлая тема

---

## Критические правила

### Транзакции (ОБЯЗАТЕЛЬНО!)

```php
try {
    DB::beginTransaction();
    // Операции
    DB::commit();
    return ['success' => true, ...];
} catch (\Throwable $e) {
    // Проверяем уровень транзакции
    if (DB::transactionLevel() > 0) {
        try {
            // rollBack() тоже может выбросить Throwable!
            DB::rollBack();
        } catch (\Throwable $rollbackException) {
            Log::error('Ошибка при откате', ['error' => $rollbackException->getMessage()]);
        }
    }

    Log::error('Основная ошибка', [
        'context' => $data,
        'error'   => $e->getMessage(),
        'trace'   => $e->getTraceAsString(),
    ]);

    return ['success' => false, 'message' => '...'];
}
```

**Почему `\Throwable`, а не `\Exception`:**
- `DB::beginTransaction()` и `DB::rollBack()` могут выбросить `\Throwable`
- `\Throwable` ловит и `Exception`, и `Error`

### Type Hints + PHPDoc

```php
/**
 * Описание метода
 *
 * @param Type $param Описание параметра
 * @return array{success: bool, data?: mixed, message: string}
 */
public function method(Type $param): array
```

### Запрещенные практики (❌)

1. Fat Controllers (логика в контроллерах)
2. Eloquent напрямую в контроллерах
3. Операции без транзакций
4. Raw SQL (используй Query Builder)
5. N+1 queries (используй `with()`)
6. Мутация props в Vue (используй `emit`)
7. Валидация в контроллере (используй Form Request)

### Обязательные практики (✅)

1. `declare(strict_types=1);` в начале PHP файлов
2. Type hints для всех параметров и возвратов
3. PHPDoc для публичных методов
4. Try-catch для критичных операций
5. Eager loading (`with()`) для отношений
6. Policies для авторизации
7. Service Providers для регистрации зависимостей
8. Константы вместо magic strings/numbers

---

## Key Models & Relations

**Session** (`app/Domains/Planning/Models/Session.php`)
- `hasMany(SessionBlock)` - блоки упражнений
- `belongsTo(Template)` - опциональный шаблон
- `belongsTo(User)`
- Статусы: `planned`, `active`, `paused`, `completed`
- Timer state хранится в localStorage на frontend

**SessionBlock** (`app/Domains/Planning/Models/SessionBlock.php`)
- `belongsTo(Session)`
- `hasMany(AudioRecording)` - записи для блока
- Поля: `planned_duration`, `actual_duration`, `started_at`, `completed_at`, `status`

**AudioRecording** (`app/Domains/Recording/Models/AudioRecording.php`)
- `belongsTo(SessionBlock)`
- Хранение в MinIO (S3-compatible)
- Временные signed URLs для доступа

**Exercise** (`app/Domains/Planning/Models/Exercise.php`)
- Справочник упражнений пользователя
- Используется для автозаполнения при создании сессий

---

## Специфика проекта

### Таймеры и состояние
- **Backend**: хранит `started_at`, `planned_duration`, `status`
- **Frontend**: localStorage для восстановления состояния таймера при перезагрузке
- Ключи: `timer-state`, `timer-session-id`
- Синхронизация каждые 5 секунд
- Звуки: start, pause, complete, warning (30 сек до конца), timeup

### Файловое хранилище (MinIO)
```php
use Illuminate\Support\Facades\Storage;

// Загрузка
Storage::disk('minio')->put($path, $contents);

// Временный URL (НЕ прямые ссылки!)
Storage::disk('minio')->temporaryUrl($path, now()->addHour());
```

### PWA Support
- `vite-plugin-pwa` настроен
- Manifest генерируется автоматически
- Оффлайн-страница для fallback
- Service Worker кеширует статику

### Интернационализация
- Vue I18n (русский/английский)
- Файлы: `resources/js/locales/ru/`, `resources/js/locales/en/`
- Переключатель в навигации
- Предпочтение сохраняется в localStorage

### Темная/светлая тема
- `useTheme()` composable
- Tailwind CSS `dark:` классы
- Предпочтение в localStorage
- Синхронизация с system preference

---

## Naming Conventions

- **PHP**: PascalCase (классы), camelCase (методы/переменные), snake_case (БД)
- **JS/Vue**: PascalCase (компоненты), camelCase (переменные), useSomething (composables)
- **Файлы**: `PascalCase.php`, `kebab-case.vue`

## Git Commits

Формат: `type: краткое описание`

**Типы:** `feat`, `fix`, `refactor`, `docs`, `test`, `chore`

**Пример:**
```
feat: добавлен Pomodoro-режим для сессий

- Создан PomodoroService для расчета слотов
- Добавлен переключатель режимов в Sessions/Create.vue
- Расширена модель Session (pomodoro_enabled, work_duration, break_duration)
```

---

## Docker Setup

```bash
make docker-setup  # Полная настройка + запуск
```

**Сервисы:**
- Nginx (порт 80)
- PHP-FPM (8.2)
- MySQL 8.0 (порт 3306)
- Redis (порт 6379)
- MinIO (порты 9000/9001, логин: minioadmin/minioadmin123)
- Node.js (сборка фронтенда)

**Приложение доступно:** http://localhost

---

## Чеклист перед коммитом

- [ ] Домен определен, структура создана
- [ ] Repository Pattern реализован
- [ ] Service Layer создан
- [ ] Type Hints + PHPDoc
- [ ] Авторизация через Policy
- [ ] Валидация через Form Request
- [ ] Транзакции для связанных операций
- [ ] Обработка ошибок с логированием
- [ ] ServiceProvider зарегистрирован
- [ ] Импорты обновлены
