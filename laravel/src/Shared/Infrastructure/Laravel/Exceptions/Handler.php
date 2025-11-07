<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Laravel\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Auth\Domain\User\Exceptions\InvalidPasswordException;
use Src\Auth\Domain\User\Exceptions\InvalidEmailException;
use Src\Auth\Domain\User\Exceptions\InvalidUserIdException;
use Src\Auth\Domain\User\Exceptions\InvalidValueObjectException;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Src\Auth\Domain\User\Exceptions\MissingUserNameException;
use Src\Auth\Domain\User\Exceptions\MultipleValidationErrorsException;
use Src\Shared\Domain\Exceptions\DomainException;
use Throwable;

/**
 * Manejador global de excepciones de dominio.
 * Convierte todas las excepciones en un formato JSON consistente.
 * 
 * Formato estándar de respuesta:
 * {
 *   "message": "Mensaje general",
 *   "errors": {
 *     "field": ["mensaje1", "mensaje2"]
 *   }
 * }
 */
class Handler
{
    public function render(Request $request, Throwable $exception): ?JsonResponse
    {
        // Manejo de múltiples errores de validación (NUEVO)
        if ($exception instanceof MultipleValidationErrorsException) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                errors: $exception->getValidationErrors(),
                statusCode: 422
            );
        }

        // Manejo específico para email ya existente (409 Conflict)
        if ($exception instanceof EmailAlreadyExistsException) {
            return $this->errorResponse(
                message: __('messages.validation.error'),
                errors: ['email' => [$exception->getMessage()]],
                statusCode: 409
            );
        }

        // Manejo específico para errores de contraseña (422 Unprocessable Entity)
        // Solo se usa si la excepción se lanza individualmente
        if ($exception instanceof InvalidPasswordException) {
            return $this->errorResponse(
                message: __('messages.validation.error'),
                errors: ['password' => $exception->getErrors()],
                statusCode: 422
            );
        }

        // Manejo específico para errores de email (422 Unprocessable Entity)
        if ($exception instanceof InvalidEmailException) {
            return $this->errorResponse(
                message: __('messages.validation.error'),
                errors: ['email' => [$exception->getMessage()]],
                statusCode: 422
            );
        }

        // Manejo específico para errores de nombre (422 Unprocessable Entity)
        if ($exception instanceof MissingUserNameException) {
            return $this->errorResponse(
                message: __('messages.validation.error'),
                errors: ['name' => [$exception->getMessage()]],
                statusCode: 422
            );
        }

        // Manejo específico para errores de ID (500 Internal Server Error)
        if ($exception instanceof InvalidUserIdException) {
            return $this->errorResponse(
                message: __('messages.unexpected_error'),
                errors: [],
                statusCode: 500
            );
        }

        // Manejo genérico para cualquier value object inválido (422)
        if ($exception instanceof InvalidValueObjectException) {
            return $this->errorResponse(
                message: __('messages.validation.error'),
                errors: ['general' => [$exception->getMessage()]],
                statusCode: 422
            );
        }

        // Manejo genérico para cualquier excepción de dominio (400 Bad Request)
        if ($exception instanceof DomainException) {
            return $this->errorResponse(
                message: $exception->getMessage(),
                errors: [],
                statusCode: 400
            );
        }

        // No es una excepción de dominio, dejar que Laravel la maneje
        return null;
    }

    /**
     * Construye una respuesta de error estandarizada.
     */
    private function errorResponse(string $message, array $errors, int $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}