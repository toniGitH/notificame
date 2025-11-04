<?php

declare(strict_types=1);

namespace Notifier\Auth\Infrastructure\Providers;

use Notifier\Auth\Application\Ports\In\RegisterUserPort;
use Notifier\Auth\Application\Ports\Out\UserRepository;
use Notifier\Auth\Application\UseCases\RegisterUserUseCase;
use Notifier\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Registrar implementaciones
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(RegisterUserPort::class, RegisterUserUseCase::class);
    }
}