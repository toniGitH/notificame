<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use Src\Auth\Application\Ports\In\RegisterUserPort;
use Src\Auth\Application\Ports\Out\UserRepository;
use Src\Auth\Domain\User\User;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use Src\Auth\Domain\User\Exceptions\InvalidEmailException;
use Src\Auth\Domain\User\Exceptions\InvalidPasswordException;
use Src\Auth\Domain\User\Exceptions\MultipleValidationErrorsException;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;

/**
 * Caso de uso para registrar un nuevo usuario en el sistema.
 * 
 * Estrategia de validación:
 * 1. Valida TODOS los campos (name, email, password) y acumula errores
 * 2. Si hay errores, lanza MultipleValidationErrorsException con TODOS
 * 3. Si no hay errores, verifica email duplicado
 * 4. Crea y persiste el usuario
 */
final class RegisterUserUseCase implements RegisterUserPort
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(array $userData): User
    {
        $validationErrors = [];
        $email = null;
        $password = null;
        $name = null;

        // 1. Validar nombre
        $name = trim($userData['name'] ?? '');
        if ($name === '') {
            $validationErrors['name'] = [__('messages.user.MISSING_USER_NAME')];
        }

        // 2. Validar email
        try {
            $email = UserEmail::fromString($userData['email'] ?? '');
        } catch (InvalidEmailException $e) {
            $validationErrors['email'] = [$e->getMessage()];
        }

        // 3. Validar password
        try {
            $password = UserPassword::fromString($userData['password'] ?? '');
        } catch (InvalidPasswordException $e) {
            $validationErrors['password'] = $e->getErrors();
        }

        // 4. Si hay errores de validación, lanzar excepción con TODOS
        if (!empty($validationErrors)) {
            throw new MultipleValidationErrorsException($validationErrors);
        }

        // 5. Verificar que el email no esté registrado
        if ($this->userRepository->exists($email)) {
            throw new EmailAlreadyExistsException($email->value());
        }

        // 6. Crear entidad User
        $user = User::create($name, $email, $password);

        // 7. Persistir usuario
        $this->userRepository->save($user);

        return $user;
    }
}