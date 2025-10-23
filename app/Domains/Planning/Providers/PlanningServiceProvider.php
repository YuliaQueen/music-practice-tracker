<?php

declare(strict_types=1);

namespace App\Domains\Planning\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Planning\Repositories\SessionRepository;
use App\Domains\Planning\Repositories\SessionBlockRepository;
use App\Domains\Planning\Contracts\SessionRepositoryInterface;
use App\Domains\Planning\Contracts\SessionBlockRepositoryInterface;

/**
 * Service Provider для домена Planning
 */
class PlanningServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрация репозитория сессий
        $this->app->bind(
            SessionRepositoryInterface::class,
            SessionRepository::class
        );

        // Регистрация репозитория блоков сессий
        $this->app->bind(
            SessionBlockRepositoryInterface::class,
            SessionBlockRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
