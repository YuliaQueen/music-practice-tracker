<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Domains\User\Providers\UserServiceProvider::class,
    App\Domains\Planning\Providers\PlanningServiceProvider::class,
    App\Domains\Recording\Providers\RecordingServiceProvider::class,
];
