<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use Src\Auth\Application\Ports\In\RegisterUserPort;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Controlador para el registro de nuevos usuarios.
 *
 * No captura excepciones de dominio: las deja subir para que el Handler global las normalice.
 */
final class RegisterUserController extends Controller
{
    public function __construct(private readonly RegisterUserPort $registerUserPort)
    {
    }

    /**
     * Invocable controller para registrar un usuario.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        // Obtener datos validados (array con name, email, password, password_confirmation)
        $validatedData = $request->validated();

        // Llamada al puerto del caso de uso.
        // Según tu interfaz RegisterUserPort::execute(array $userData): User
        $user = $this->registerUserPort->execute($validatedData);

        return new JsonResponse([
            'message' => __('messages.user.registered_success'),
            'user' => [
                'id' => $user->id()->value(),
                'name' => $user->name()->value(),
                'email' => $user->email()->value(),
            ]
        ], 201);
    }
}
