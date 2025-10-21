<?php

declare(strict_types=1);

namespace App\Domains\User\Providers;

use App\Domains\User\Repositories\UserRepository;
use App\Domains\User\Repositories\UserRepositoryInterface;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

/**
 * Сервис-провайдер для домена пользователей
 *
 * Регистрирует сервисы, репозитории и наблюдателей домена
 */
class UserServiceProvider extends ServiceProvider
{
    /**
     * Зарегистрировать сервисы приложения
     */
    public function register(): void
    {
        $this->registerRepositories();
    }

    /**
     * Зарегистрировать репозитории домена
     */
    private function registerRepositories(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
    }

    /**
     * Загрузить сервисы приложения
     */
    public function boot(): void
    {
        // Будем добавлять observers и events позже
    }
}
