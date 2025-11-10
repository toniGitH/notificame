<?php

declare(strict_types=1);

namespace Tests\System;

use Tests\TestCase;

final class CheckEnvTest extends TestCase
{
    // Verifica que los tests se ejecutan en entorno testing con SQLite en memoria
    public function test_verifica_entorno_de_testing_y_base_de_datos_sqlite_en_memoria(): void
    {
        // Verificar que estamos en entorno de testing
        $this->assertEquals('testing', config('app.env'));
        
        // Verificar que usamos SQLite
        $this->assertEquals('sqlite', config('database.default'));
        
        // Verificar que la base de datos es :memory:
        $this->assertEquals(':memory:', config('database.connections.sqlite.database'));
        
        // Información adicional (solo para debug si es necesario)
        $envInfo = [
            'APP_ENV' => config('app.env'),
            'DB_CONNECTION' => config('database.default'),
            'DB_DATABASE' => config('database.connections.sqlite.database'),
        ];
        
        // Descomentar si quieres ver la info durante los tests
        dump($envInfo);
    }
}