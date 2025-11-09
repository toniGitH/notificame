<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Laravel\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

use Src\Shared\Domain\Exceptions\DomainException;
use Src\Shared\Domain\Exceptions\InvalidValueObjectException;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Src\Shared\Domain\Exceptions\MultipleDomainException;

final class Handler
{
    public function handle(Request $request, Throwable $e): ?JsonResponse
    {
        // 1) Validación de Request (ValidationException)
        if ($e instanceof ValidationException) {
            return $this->errorResponse(
                __('messages.validation.error'), 
                $e->errors(), 
                422
            );
        }

        // 2) Email duplicado
        if ($e instanceof EmailAlreadyExistsException) {
            return $this->errorResponse(
                __('messages.validation.error'), 
                ['email' => [__($e->getMessage())]], 
                422
            );
        }

        // 3) Excepciones de ValueObjects (captura genérica)
        if ($e instanceof InvalidValueObjectException) {
            $field = $this->guessFieldFromException($e);
            return $this->errorResponse(
                __('messages.validation.error'), 
                [$field => [$e->getMessage()]], 
                422
            );
        }

        // 4) Excepciones compuestas de dominio (MultipleDomainException)
        if ($e instanceof MultipleDomainException) {
            return $this->errorResponse(
                __('messages.validation.error'),
                $e->errors(),
                422
            );
        }

        // 5) Otros errores de dominio
        if ($e instanceof DomainException) {
            return $this->errorResponse($e->getMessage(), [], 400);
        }

        // 6) HTTP exceptions
        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'message' => $e->getMessage() ?: __('messages.unexpected_error')
            ], $e->getStatusCode());
        }

        // 7) Error inesperado
        return null;
    }

    private function errorResponse(string $message, array $errors, int $statusCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    private function guessFieldFromException(Throwable $e): string
    {
        $className = class_basename($e);
        
        return match($className) {
            'InvalidEmailException', 'EmptyEmailException' => 'email',
            'InvalidPasswordException', 'EmptyPasswordException' => 'password',
            'InvalidUserNameException', 'EmptyUserNameException' => 'name',
            'InvalidUserIdException' => 'id',
            default => $this->guessFieldFromMessage($e->getMessage())
        };
    }

    private function guessFieldFromMessage(string $message): string
    {
        $lower = mb_strtolower($message);

        if (str_contains($lower, 'email') || str_contains($lower, 'correo')) {
            return 'email';
        }

        if (str_contains($lower, 'contraseña') || str_contains($lower, 'password')) {
            return 'password';
        }

        if (str_contains($lower, 'nombre') || str_contains($lower, 'name')) {
            return 'name';
        }

        return 'data';
    }
}
