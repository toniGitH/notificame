<?php

declare(strict_types=1);

namespace Notifier\Auth\Application\UseCases;

use Notifier\Auth\Application\Ports\In\RegisterUserPort;
use Notifier\Auth\Application\Ports\Out\UserRepository;
use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use Notifier\Auth\Domain\User\ValueObjects\UserPassword;
use Notifier\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use InvalidArgumentException;
use RuntimeException;

final class RegisterUserUseCase implements RegisterUserPort
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function execute(array $userData): User
    {
        // Validación básica de campos requeridos
        if (!isset($userData['name'], $userData['email'], $userData['password'])) {
            throw new InvalidArgumentException('Missing required fields');
        }

        // Crear el Value Object de email
        $email = UserEmail::fromString($userData['email']);

        // Verificar si el email ya existe
        if ($this->userRepository->exists($email)) {
            throw new EmailAlreadyExistsException();
        }

          // Crear Value Object de password (con validación y hash interno)
        $passwordVO = UserPassword::fromString($userData['password']);

        // Crear usuario (entidad de dominio, no Model Eloquent)
        // Las validaciones de formato se hacen en los Value Objects
        $user = User::create(
            $userData['name'],
            $email,
            $passwordVO
        );

        // Persistir usuario
        $this->userRepository->save($user);

        return $user;
    }
}