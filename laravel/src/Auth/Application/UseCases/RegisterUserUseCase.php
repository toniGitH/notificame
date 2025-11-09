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
use Src\Shared\Domain\Exceptions\InvalidValueObjectException;
use Src\Shared\Domain\Exceptions\MultipleDomainException;

final class RegisterUserUseCase implements RegisterUserPort
{
    private readonly UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(array $userData): User
    {
        $errors = [];

        // 1. Crear Value Objects y acumular errores
        try {
            $name = UserName::fromString($userData['name'] ?? '');
        } catch (InvalidValueObjectException $e) {
            $errors['name'][] = $e->getMessage();
        }

        try {
            $email = UserEmail::fromString($userData['email'] ?? '');
        } catch (InvalidValueObjectException $e) {
            $errors['email'][] = $e->getMessage();
        }

        try {
            $password = UserPassword::fromString($userData['password'] ?? '');
        } catch (InvalidValueObjectException $e) {
            $errors['password'][] = $e->getMessage();
        }

        // 2. Verificar unicidad del email solo si no hay errores en email
        if (!isset($errors['email']) && $this->userRepository->exists($email)) {
            $errors['email'][] = __('messages.user.EMAIL_ALREADY_EXISTS');
        }

        // 3. Si hay errores acumulados, lanzamos excepción compuesta
        if (!empty($errors)) {
            throw new MultipleDomainException($errors);
        }

        // 4. Crear entidad User
        $user = User::create($name, $email, $password);

        // 5. Persistir usuario
        $this->userRepository->save($user);

        return $user;
    }
}
