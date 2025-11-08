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

final class RegisterUserUseCase implements RegisterUserPort
{
    private readonly UserRepository $userRepository;

    public function __construct( UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(array $userData): User
    {
        // 1. Crear Value Objects (lanzan excepciones automáticamente si inválidos)
        $name = UserName::fromString($userData['name'] ?? '');
        $email = UserEmail::fromString($userData['email'] ?? '');
        $password = UserPassword::fromString($userData['password'] ?? '');

        // 2. Verificar que el email no esté registrado (mediante llamada al puerto de salida)
        if ($this->userRepository->exists($email)) {
            throw new EmailAlreadyExistsException($email->value());
        }

        // 3. Crear entidad User
        $user = User::create($name, $email, $password);

        // 4. Persistir usuario (mediante llamada al puerto de salida)
        $this->userRepository->save($user);

        return $user;
    }
}