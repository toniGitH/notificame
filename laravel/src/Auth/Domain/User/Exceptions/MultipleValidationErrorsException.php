<?php

declare(strict_types=1);

namespace Src\Auth\Domain\User\Exceptions;

use Src\Shared\Domain\Exceptions\DomainException;

/**
 * Excepción lanzada cuando hay múltiples errores de validación de diferentes campos.
 * Acumula todos los errores para reportarlos de una vez al usuario.
 * 
 * Formato de errores: ['field' => ['mensaje1', 'mensaje2'], 'field2' => ['mensaje']]
 */
final class MultipleValidationErrorsException extends DomainException
{
    private array $validationErrors = [];

    /**
     * @param array $validationErrors Array asociativo: ['field' => ['mensaje1', 'mensaje2']]
     */
    public function __construct(array $validationErrors)
    {
        $this->validationErrors = $validationErrors;
        
        parent::__construct(__('messages.validation.error'));
    }

    /**
     * Obtiene todos los errores de validación agrupados por campo
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}