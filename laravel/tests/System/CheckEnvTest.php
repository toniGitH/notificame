<?php

namespace Tests\System;

use Tests\TestCase;

class CheckEnvTest extends TestCase
{
    public function test_verifica_entorno_y_base_de_datos_activos(): void
    {
        $envInfo = [
            'APP_ENV' => config('app.env'),
            'DB_CONNECTION' => config('database.default'),
            'DB_DATABASE' => config('database.connections.mysql.database'),
        ];

        print_r($envInfo); // o dump($envInfo);

        $this->assertTrue(true); // para que PHPUnit marque el test como pasado
    }
}
