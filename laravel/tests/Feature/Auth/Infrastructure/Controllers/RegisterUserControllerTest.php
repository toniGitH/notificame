<?php

declare(strict_types=1);

namespace Tests\Feature\Auth\Infrastructure\Controllers;

use Tests\TestCase;

/**
 * TEST DE FEATURE (End-to-End)
 * 
 * Testea el RegisterUserController con el flujo HTTP completo:
 * HTTP Request → Validación → Controller → UseCase → Repository → BD → Response
 * 
 * Este es el ÚNICO tipo de test apropiado para un controller.
 * Prueba la funcionalidad real como lo haría un usuario/cliente HTTP.
 */
final class RegisterUserControllerTest extends TestCase
{
    private string $endpoint = '/api/auth/register';

    // TEST PENDIENTE DE DESARROLLAR
}