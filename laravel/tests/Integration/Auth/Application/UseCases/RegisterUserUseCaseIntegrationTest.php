<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\UseCases;

use Src\Auth\Application\UseCases\RegisterUserUseCase;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Src\Auth\Domain\User\Exceptions\InvalidEmailFormatException;
use Src\Auth\Domain\User\Exceptions\PasswordTooShortException;
use Src\Auth\Domain\User\Exceptions\MissingUserNameException;
use Src\Auth\Domain\User\User;
use Src\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Tests\TestCase;
use App\Models\User as EloquentUser;

/**
 * TEST DE INTEGRACIÓN
 * Testea el RegisterUserUseCase usando repositorio Eloquent y base de datos real.
 */
final class RegisterUserUseCaseIntegrationTest extends TestCase
{
    private RegisterUserUseCase $useCase;
    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentUserRepository();
        $this->useCase = new RegisterUserUseCase($this->repository);
    }

    // TEST PENDIENTE DE DESARROLLAR
}
