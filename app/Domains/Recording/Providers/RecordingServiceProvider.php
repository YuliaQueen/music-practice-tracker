<?php

declare(strict_types=1);

namespace App\Domains\Recording\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Recording\Repositories\AudioRecordingRepository;
use App\Domains\Recording\Contracts\AudioRecordingRepositoryInterface;

/**
 * Service Provider для домена Recording
 */
class RecordingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрация репозитория
        $this->app->bind(
            AudioRecordingRepositoryInterface::class,
            AudioRecordingRepository::class
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
