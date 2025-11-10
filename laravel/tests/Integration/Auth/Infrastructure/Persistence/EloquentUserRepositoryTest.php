<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructure\Persistence;

use App\Models\User as EloquentUser;
use Illuminate\Database\QueryException;
use Src\Auth\Domain\User\User;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use Src\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Tests\TestCase;

/**
 * TEST DE INTEGRACIÓN
 * 
 * Testea el EloquentUserRepository con la base de datos real.
 * Verifica que el adaptador de infraestructura traduce correctamente
 * entre objetos de dominio (User) y modelos de Eloquent (EloquentUser).
 * 
 * Este es el ÚNICO tipo de test apropiado para un repositorio.
 * No se hacen tests unitarios porque el valor está en verificar
 * la interacción real con la base de datos.
 */
final class EloquentUserRepositoryTest extends TestCase
{
    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = new EloquentUserRepository();
    }

    // TEST PENDIENTE DE DESARROLLAR
 }