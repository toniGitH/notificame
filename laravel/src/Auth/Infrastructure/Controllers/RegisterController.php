<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use Src\Auth\Application\Ports\In\RegisterUserPort;
use Illuminate\Http\JsonResponse;

/**
 * Controlador para el registro de nuevos usuarios.
 * 
 * Este controlador NO captura excepciones de dominio.
 * Todas las excepciones se manejan automáticamente en el Handler global.
 */
final class RegisterController
{
    public function __construct(
        private readonly RegisterUserPort $registerUserPort
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        // Los datos ya están validados por RegisterRequest (validaciones básicas de Laravel)
        $validatedData = $request->validated();
        
        // El caso de uso maneja la lógica de negocio y lanza excepciones si es necesario
        // Esas excepciones serán capturadas automáticamente por el Handler global
        $user = $this->registerUserPort->execute($validatedData);

        return new JsonResponse([
            'message' => __('messages.user.registered_success'),
            'user' => [
                'id' => $user->id()->value(),
                'name' => $user->name(),
                'email' => $user->email()->value(),
            ]
        ], 201);
    }
}