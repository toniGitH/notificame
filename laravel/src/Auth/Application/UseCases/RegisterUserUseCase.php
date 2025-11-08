<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use Src\Auth\Application\Ports\In\RegisterUserPort;
use Src\Auth\Application\Ports\Out\UserRepository;
use Src\Auth\Domain\User\User;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use Src\Auth\Domain\User\ValueObjects\UserName;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;

/**
 * Caso de uso para registrar un nuevo usuario en el sistema.
 * 
 * IMPORTANTE: Las excepciones de los Value Objects suben automáticamente
 * al Handler global que las normaliza al formato estándar.
 */
final class RegisterUserUseCase implements RegisterUserPort
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(array $userData): User
    {
        // 1. Crear Value Objects (lanzan excepciones automáticamente si inválidos)
        $email = UserEmail::fromString($userData['email'] ?? '');
        $password = UserPassword::fromString($userData['password'] ?? '');
        $name = UserName::fromString($userData['name'] ?? '');

        // 2. Verificar que el email no esté registrado
        if ($this->userRepository->exists($email)) {
            throw new EmailAlreadyExistsException($email->value());
        }

        // 3. Crear entidad User
        $user = User::create(
            $name->value(), 
            $email->value(), 
            $password->value()
        );

        // 4. Persistir usuario
        $this->userRepository->save($user);

        return $user;
    }
}