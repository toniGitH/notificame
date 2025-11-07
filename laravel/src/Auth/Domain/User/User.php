<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User;

use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserId;
use Src\Auth\Domain\User\ValueObjects\UserPassword;

/**
 * Entidad User del contexto Auth.
 * Representa un usuario registrado en el sistema.
 * 
 * NOTA: La validación del nombre se realiza en el caso de uso,
 * no en la entidad, para poder acumular todos los errores.
 */
final class User
{
    private function __construct(
        private UserId $id,
        private string $name,
        private UserEmail $email,
        private UserPassword $password
    ) {}

    /**
     * Crea un nuevo usuario.
     * La validación del nombre debe hacerse ANTES de llamar a este método.
     * 
     * @param string $name Nombre del usuario (ya validado)
     * @param UserEmail $email Email del usuario
     * @param UserPassword $password Contraseña del usuario
     * @return self
     */
    public static function create(string $name, UserEmail $email, UserPassword $password): self
    {
        $id = UserId::generate();
        
        return new self($id, $name, $email, $password);
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function password(): UserPassword
    {
        return $this->password;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            'name' => $this->name,
            'email' => $this->email->value(),
            'password' => $this->password->value()
        ];
    }
}