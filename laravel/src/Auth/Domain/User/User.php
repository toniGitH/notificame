<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User;

use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserId;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use Src\Auth\Domain\User\ValueObjects\UserName;

/**
 * Entidad User del contexto Auth.
 * Representa un usuario registrado en el sistema.
 * 
 * GARANTIZA INTEGRIDAD COMPLETA: Todos sus atributos son válidos por construcción.
 * No permite la creación de entidades con datos inválidos.
 */
final class User
{
    private function __construct(
        private UserId $id,
        private UserName $name,
        private UserEmail $email,
        private UserPassword $password
    ) {}

    /**
     * Crea un nuevo usuario.
     * 
     * NOTA: Este método garantiza que solo se crearán entidades válidas.
     * Si algún parámetro es inválido, se lanzará la excepción correspondiente.
     * 
     * @param string $name Nombre del usuario
     * @param string $email Email del usuario  
     * @param string $password Contraseña del usuario
     * @return self
     */
    public static function create(string $name, string $email, string $password): self
    {
        $id = UserId::generate();
        $userName = UserName::fromString($name);
        $userEmail = UserEmail::fromString($email);
        $userPassword = UserPassword::fromString($password);
        
        return new self($id, $userName, $userEmail, $userPassword);
    }

    public function id(): UserId
    {
        return $this->id;
    }

    /**
     * Retorna el UserName Value Object.
     * 
     * @return UserName
     */
    public function name(): UserName
    {
        return $this->name;
    }

    /**
     * Retorna el nombre como string (método de conveniencia).
     * 
     * @return string
     */
    public function nameValue(): string
    {
        return $this->name->value();
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
            'name' => $this->name->value(),
            'email' => $this->email->value(),
            'password' => $this->password->value()
        ];
    }
}