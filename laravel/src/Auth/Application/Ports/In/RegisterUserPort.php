<?php

declare(strict_types=1);

namespace Notifier\Auth\Application\Ports\In;

use Notifier\Auth\Domain\User\User;

interface RegisterUserPort
{
    /**
     * Registra un nuevo usuario en el sistema
     *
     * @param array $userData Los datos del usuario a registrar
     * @return User El usuario registrado
     * @throws \InvalidArgumentException Si los datos son inválidos
     * @throws \DomainException Si el email ya existe
     */
    public function execute(array $userData): User;
}
