<?php

declare(strict_types=1);

namespace Src\Auth\Application\UseCases;

use Src\Auth\Application\Ports\In\RegisterUserPort;
use Src\Auth\Application\Ports\Out\UserRepository;
use Src\Auth\Domain\User\User;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use Src\Auth\Domain\User\ValueObjects\UserName;
use Src\Auth\Domain\User\Exceptions\InvalidEmailException;
use Src\Auth\Domain\User\Exceptions\InvalidPasswordException;
use Src\Auth\Domain\User\Exceptions\InvalidUserNameException;
use Src\Auth\Domain\User\Exceptions\MissingUserNameException;
use Src\Auth\Domain\User\Exceptions\MultipleValidationErrorsException;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;

/**
 * Caso de uso para registrar un nuevo usuario en el sistema.
 * 
 * ESTRATEGIA MEJORADA V2:
 * 1. Validar email, password y name usando Value Objects
 * 2. Los VO lanzan excepciones automáticamente si son inválidos
 * 3. Recoger TODOS los errores y lanzar MultipleValidationErrorsException
 * 4. Crear entidad User (que garantiza integridad completa)
 * 5. Verificar email duplicado
 * 6. Persistir usuario
 * 
 * ESTRUCTURA DE RESPUESTA UNIFORME:
 * - Mantiene el mismo formato de error tanto desde Request como desde Domain
 * - MultipleValidationErrorsException con errores estructurados por campo
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

        // 1. Validar email (el VO lanza excepción si es inválido)
        try {
            $email = UserEmail::fromString($userData['email'] ?? '');
        } catch (InvalidEmailException $e) {
            $validationErrors['email'] = [$e->getMessage()];
        }

        // 2. Validar password (el VO lanza excepción si es inválido)
        try {
            $password = UserPassword::fromString($userData['password'] ?? '');
        } catch (InvalidPasswordException $e) {
            $validationErrors['password'] = $e->getErrors();
        }

        // 3. Validar name (el VO lanza excepción si es inválido)
        try {
            $name = UserName::fromString($userData['name'] ?? '');
        } catch (MissingUserNameException $e) {
            $validationErrors['name'] = [$e->getMessage()];
        } catch (InvalidUserNameException $e) {
            // Maneja tanto errores simples como múltiples errores del VO
            if ($e->hasMultipleErrors()) {
                $validationErrors['name'] = $e->getErrors();
            } else {
                $validationErrors['name'] = [$e->getMessage()];
            }
        }

        // 4. Si hay errores de validación, lanzar excepción con TODOS
        if (!empty($validationErrors)) {
            throw new MultipleValidationErrorsException($validationErrors);
        }

        // 5. Crear entidad User (que valida automáticamente todos los atributos)
        // NOTA: A este punto, todos los datos están garantizados como válidos
        $user = User::create(
            $name->value(), 
            $email->value(), 
            $password->value()
        );

        // 6. Verificar que el email no esté registrado
        if ($this->userRepository->exists($email)) {
            throw new EmailAlreadyExistsException($email->value());
        }

        // 7. Persistir usuario
        $this->userRepository->save($user);

        return $user;
    }
}