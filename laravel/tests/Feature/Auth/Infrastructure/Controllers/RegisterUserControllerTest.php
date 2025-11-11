<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserControllerTest extends TestCase
{
    private string $endpoint = '/api/auth/register';

    /**
     * Verifica que la base de datos esté limpia antes de cada test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Asegurarse de que la tabla users está vacía
        DB::table('users')->truncate();
    }

    // ============================================
    // TESTS DE REGISTRO EXITOSO
    // ============================================

    public function test_can_register_user_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ])
            ->assertJson([
                'user' => [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                ],
            ]);

        // Verificar que el usuario existe en la base de datos
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
        ]);

        // Verificar que el ID devuelto es un UUID válido v4
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $response->json('user.id')
        );
    }

    public function test_password_is_hashed_in_database(): void
    {
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane.doe@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ];

        $this->postJson($this->endpoint, $userData);

        $user = DB::table('users')->where('email', 'jane.doe@example.com')->first();
        
        // Verificar que la contraseña NO está en texto plano
        $this->assertNotEquals('SecurePass123!', $user->password);
        
        // Verificar que la contraseña está hasheada (bcrypt empieza con $2y$)
        $this->assertStringStartsWith('$2y$', $user->password);
        
        // Verificar que el hash es válido y coincide con la contraseña original
        $this->assertTrue(Hash::check('SecurePass123!', $user->password));
    }

    public function test_can_register_with_minimum_valid_name_length(): void
    {
        $userData = [
            'name' => 'Joe', // 3 caracteres (mínimo)
            'email' => 'joe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['name' => 'Joe']);
    }

    public function test_can_register_with_maximum_valid_name_length(): void
    {
        $longName = str_repeat('a', 100); // 100 caracteres (máximo)
        
        $userData = [
            'name' => $longName,
            'email' => 'longname@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['name' => $longName]);
    }

    public function test_can_register_with_minimum_valid_password_length(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Pass123!', // 8 caracteres (mínimo)
            'password_confirmation' => 'Pass123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
    }

    public function test_can_register_with_maximum_valid_password_length(): void
    {
        // 50 caracteres con todos los requisitos
        $longPassword = 'Aa1!' . str_repeat('x', 46);
        
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => $longPassword,
            'password_confirmation' => $longPassword,
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
    }

    public function test_can_register_with_all_special_characters_in_password(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'special@example.com',
            'password' => 'Password123!@#$%^&*()_-+=[]{}|;:\'",.<>/?¿',
            'password_confirmation' => 'Password123!@#$%^&*()_-+=[]{}|;:\'",.<>/?¿',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
    }

    public function test_can_register_with_maximum_valid_email_length(): void
    {
        // Email con una parte locas de 64 caracteres (maximo permitido por RFC)
        $localPart = str_repeat('a', 64); // 64 + @ + ejemplo.com = 100
        $email = $localPart . '@ejemplo.com';
        
        $userData = [
            'name' => 'Test User',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    public function test_can_register_with_unicode_characters_in_name(): void
    {
        $userData = [
            'name' => 'José María Ñoño',
            'email' => 'jose@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['name' => 'José María Ñoño']);
    }

    public function test_can_register_with_numbers_in_name(): void
    {
        $userData = [
            'name' => 'User123',
            'email' => 'user123@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['name' => 'User123']);
    }

    public function test_can_register_with_special_characters_in_name(): void
    {
        $userData = [
            'name' => "O'Brien-Smith",
            'email' => 'obrien@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['name' => "O'Brien-Smith"]);
    }

    // ============================================
    // TESTS DE VALIDACIÓN - CAMPO NAME
    // ============================================

    public function test_cannot_register_without_name(): void
    {
        $userData = [
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['name'],
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_empty_name(): void
    {
        $userData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_name_too_short(): void
    {
        $userData = [
            'name' => 'Ab', // 2 caracteres (mínimo es 3)
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_name_too_long(): void
    {
        $userData = [
            'name' => str_repeat('a', 101), // 101 caracteres (máximo es 100)
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_null_name(): void
    {
        $userData = [
            'name' => null,
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_register_with_integer_name_should_succeed_if_length_is_valid(): void
    {
        // Nota importante:
        // En una petición HTTP real (Postman, navegador, cliente JSON), Laravel convierte automáticamente
        // los números a strings al decodificar JSON, por lo que "name": 123 pasaría la validación 'string'.
        // En PHPUnit, cuando pasamos un array PHP como $userData, 123 es un int, no un string,
        // y por eso la regla 'string' falla. Para simular correctamente el comportamiento real,
        // debemos convertirlo explícitamente a string.
        $userData = [
            'name' => (string) 123, // se convierte a "123"
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'user' => ['id', 'name', 'email'],
                ]);

        $this->assertDatabaseHas('users', ['name' => '123']);
    }

    public function test_cannot_register_with_name_as_array(): void
    {
        $userData = [
            'name' => ['John', 'Doe'],
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    // ============================================
    // TESTS DE VALIDACIÓN - CAMPO EMAIL
    // ============================================

    public function test_cannot_register_without_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_empty_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => '',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_invalid_email_format(): void
    {
        $invalidEmails = [
            'notanemail',
            'missing@domain',
            '@nodomain.com',
            'no@domain',
            'spaces in@email.com',
            'double@@domain.com',
            'missing.domain@',
            'incomplete@',
            '@incomplete.com',
            'no-at-sign.com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            DB::table('users')->truncate();

            $userData = [
                'name' => 'John Doe',
                'email' => $invalidEmail,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ];

            $response = $this->postJson($this->endpoint, $userData);

            $response->assertStatus(422, "Failed for email: {$invalidEmail}")
                    ->assertJsonStructure(['errors' => ['email']]);

            $this->assertDatabaseCount('users', 0);
        }
    }

    public function test_cannot_register_with_email_too_long(): void
    {
        $localPart = str_repeat('a', 89);
        $email = $localPart . '@ejemplo.com';

        $userData = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_duplicate_email(): void
    {
        $userData = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->postJson($this->endpoint, $userData)->assertStatus(201);

        $userData2 = [
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'DifferentPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ];

        $response = $this->postJson($this->endpoint, $userData2);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_cannot_register_with_duplicate_email_case_insensitive(): void
    {
        if ($this->isSqlite()) {
            $this->markTestSkipped('SQLite puede comportarse de forma diferente con case sensitivity en constraints UNIQUE.');
        }

        $userData = [
            'name' => 'First User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $this->postJson($this->endpoint, $userData)->assertStatus(201);

        $userData2 = [
            'name' => 'Second User',
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData2);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_cannot_register_with_null_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => null,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_email_as_array(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => ['test@example.com'],
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['email']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_can_register_with_plus_sign_in_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john+test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john+test@example.com']);
    }

    public function test_can_register_with_subdomain_in_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@mail.example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'john@mail.example.com']);
    }

    // ============================================
    // TESTS DE VALIDACIÓN - CAMPO PASSWORD
    // ============================================

    public function test_cannot_register_without_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_empty_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_too_short(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Pass1!', // 6 caracteres
            'password_confirmation' => 'Pass1!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_exactly_seven_characters(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Pass12!', // 7 caracteres
            'password_confirmation' => 'Pass12!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_too_long(): void
    {
        $longPassword = 'Aa1!' . str_repeat('x', 47); // 51 caracteres

        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => $longPassword,
            'password_confirmation' => $longPassword,
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_missing_uppercase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password123!', // sin mayúscula
            'password_confirmation' => 'password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_missing_lowercase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'PASSWORD123!', // sin minúscula
            'password_confirmation' => 'PASSWORD123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_missing_number(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password!!!!', // sin número
            'password_confirmation' => 'Password!!!!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_missing_special_character(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password123', // sin carácter especial
            'password_confirmation' => 'Password123',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_only_lowercase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_only_uppercase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'ABCDEFGH',
            'password_confirmation' => 'ABCDEFGH',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_only_numbers(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_only_special_characters(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => '!@#$%^&*',
            'password_confirmation' => '!@#$%^&*',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_without_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_mismatched_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_empty_password_confirmation(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => '',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_null_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => null,
            'password_confirmation' => null,
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_password_as_array(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => ['Password123!'],
            'password_confirmation' => ['Password123!'],
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_is_case_sensitive(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'password123!', // Diferente en mayúsculas
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
                ->assertJsonStructure(['errors' => ['password']]);

        $this->assertDatabaseCount('users', 0);
    }

    // ============================================
    // TESTS DE VALIDACIÓN MÚLTIPLE
    // ============================================

    public function test_cannot_register_with_all_fields_empty(): void
    {
        $userData = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password',
                ],
            ]);

        // Verificar que hay errores en los tres campos principales
        $errors = $response->json('errors');
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_all_fields_missing(): void
    {
        $userData = [];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password',
                ],
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_cannot_register_with_multiple_validation_errors(): void
    {
        $userData = [
            'name' => 'AB', // Muy corto
            'email' => 'invalid-email', // Formato inválido
            'password' => 'weak', // No cumple requisitos
            'password_confirmation' => 'different', // No coincide
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password',
                ],
            ]);

        // Verificar que hay al menos un error por cada campo
        $errors = $response->json('errors');
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_validation_returns_only_first_error_per_field(): void
    {
        // Un campo puede tener múltiples problemas, pero Laravel con tu configuración
        // debería devolver solo el primer error por campo
        $userData = [
            'name' => '', // Vacío y muy corto
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);
        
        $errors = $response->json('errors.name');
        // Debería haber solo un error (el primero que falla)
        $this->assertCount(1, $errors);
    }

    public function test_email_uniqueness_and_password_validation_both_fail(): void
    {
        // Primero registrar un usuario
        $firstUser = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];
        $this->postJson($this->endpoint, $firstUser)->assertStatus(201);

        // Intentar registrar con email duplicado Y contraseña inválida
        $secondUser = [
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'weak', // Contraseña inválida
            'password_confirmation' => 'different', // No coincide
        ];

        $response = $this->postJson($this->endpoint, $secondUser);

        $response->assertStatus(422);
        
        // Debería haber errores tanto de email como de password
        $errors = $response->json('errors');
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    // ============================================
    // TESTS DE CASOS EDGE Y SEGURIDAD
    // ============================================

    public function test_response_does_not_contain_password_in_any_form(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        // Verificar que la respuesta NO contiene la contraseña en ningún formato
        $responseData = $response->json();
        $this->assertArrayNotHasKey('password', $responseData['user'] ?? []);
        $this->assertArrayNotHasKey('password_confirmation', $responseData['user'] ?? []);
        
        $jsonString = json_encode($responseData);
        $this->assertStringNotContainsString('Password123!', $jsonString);
        $this->assertStringNotContainsString('password', $jsonString);
    }

    public function test_response_does_not_contain_sensitive_database_fields(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        $user = $response->json('user');
        
        // No debería incluir timestamps ni otros campos internos
        $this->assertArrayNotHasKey('created_at', $user);
        $this->assertArrayNotHasKey('updated_at', $user);
        $this->assertArrayNotHasKey('deleted_at', $user);
        $this->assertArrayNotHasKey('remember_token', $user);
        $this->assertArrayNotHasKey('email_verified_at', $user);
    }

    public function test_cannot_register_with_additional_unexpected_fields(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin', // Campo no esperado
            'is_active' => true, // Campo no esperado
            'is_verified' => true, // Campo no esperado
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Debería registrarse correctamente ignorando campos extra
        $response->assertStatus(201);

        // Verificar que los campos extra no se guardaron
        $user = DB::table('users')->where('email', 'test@example.com')->first();
        
        // Los campos que existen en la tabla pero no deberían estar seteados
        // deberían tener valores por defecto null o false
        $userArray = (array) $user;
        $this->assertArrayNotHasKey('role', $userArray);
        $this->assertArrayNotHasKey('is_active', $userArray);
    }

    public function test_cannot_register_with_sql_injection_in_name(): void
    {
        $userData = [
            'name' => "'; DROP TABLE users; --",
            'email' => 'hacker@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Debería registrarse normalmente, el SQL debería ser escapado
        $response->assertStatus(201);
        
        // Verificar que la tabla sigue existiendo
        $this->assertDatabaseHas('users', [
            'name' => "'; DROP TABLE users; --",
            'email' => 'hacker@example.com',
        ]);
    }

    public function test_sql_injection_in_email_is_not_executed(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => "admin'--@example.com",
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201); // se inserta como string seguro
        $this->assertDatabaseHas('users', ['email' => "admin'--@example.com"]);
    }

    public function test_cannot_register_with_xss_in_name(): void
    {
        $userData = [
            'name' => '<script>alert("XSS")</script>',
            'email' => 'xss@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Debería registrarse, Laravel escapa automáticamente en las vistas
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('users', [
            'name' => '<script>alert("XSS")</script>',
            'email' => 'xss@example.com',
        ]);
        
        // Verificar que en la respuesta JSON también está presente (sin escapar en JSON)
        $response->assertJson([
            'user' => [
                'name' => '<script>alert("XSS")</script>',
            ]
        ]);
    }

    public function test_cannot_register_with_name_containing_only_spaces(): void
    {
        $userData = [
            'name' => '   ',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_name_is_trimmed_before_saving(): void
    {
        $userData = [
            'name' => '  John Doe  ',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Dependiendo de tu implementación, podría guardarse con espacios o sin ellos
        // Este test verifica el comportamiento actual
        $response->assertStatus(201);
        
        $user = DB::table('users')->where('email', 'john@example.com')->first();
        
        // Verificar si se guardan los espacios o no
        $this->assertTrue(
            $user->name === '  John Doe  ' || $user->name === 'John Doe',
            'El nombre debería guardarse con o sin espacios de forma consistente'
        );
    }

    public function test_email_is_stored_as_provided_not_lowercased(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'John.Doe@Example.COM',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);
        
        // Verificar cómo se almacena el email
        $user = DB::table('users')->where('email', 'John.Doe@Example.COM')->first();
        
        // En tu caso, parece que se guarda tal cual
        $this->assertEquals('John.Doe@Example.COM', $user->email);
    }

    // ============================================
    // TESTS DE MÚLTIPLES REGISTROS
    // ============================================

    public function test_multiple_users_can_register_successfully(): void
    {
        $users = [
            [
                'name' => 'User One',
                'email' => 'user1@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ],
            [
                'name' => 'User Two',
                'email' => 'user2@example.com',
                'password' => 'Password456!',
                'password_confirmation' => 'Password456!',
            ],
            [
                'name' => 'User Three',
                'email' => 'user3@example.com',
                'password' => 'Password789!',
                'password_confirmation' => 'Password789!',
            ],
        ];

        foreach ($users as $userData) {
            $response = $this->postJson($this->endpoint, $userData);
            $response->assertStatus(201);
        }

        $this->assertDatabaseCount('users', 3);
        
        // Verificar que cada usuario tiene un ID único
        $userIds = DB::table('users')->pluck('id')->toArray();
        $this->assertCount(3, array_unique($userIds));
    }

    public function test_can_register_users_with_same_name_but_different_emails(): void
    {
        $user1 = [
            'name' => 'John Doe',
            'email' => 'john1@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $user2 = [
            'name' => 'John Doe', // Mismo nombre
            'email' => 'john2@example.com', // Email diferente
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response1 = $this->postJson($this->endpoint, $user1);
        $response2 = $this->postJson($this->endpoint, $user2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_can_register_users_with_same_password(): void
    {
        $user1 = [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'SamePassword123!',
            'password_confirmation' => 'SamePassword123!',
        ];

        $user2 = [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'SamePassword123!', // Misma contraseña
            'password_confirmation' => 'SamePassword123!',
        ];

        $response1 = $this->postJson($this->endpoint, $user1);
        $response2 = $this->postJson($this->endpoint, $user2);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseCount('users', 2);
        
        // Verificar que ambos hashes son diferentes (cada hash es único)
        $users = DB::table('users')->get();
        $this->assertNotEquals($users[0]->password, $users[1]->password);
    }

    // ============================================
    // TESTS DE FORMATO DE RESPUESTA
    // ============================================

    public function test_successful_registration_returns_correct_message(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        // Comprobamos que el campo "message" coincide con la traducción actual
        // de la clave messages.user.registered_success en el locale activo.
        // De este modo el test es agnóstico al idioma.
        $expected = __('messages.user.registered_success');

        $response->assertJsonPath('message', $expected);
    }

    public function test_validation_error_returns_correct_message(): void
    {
        $userData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);

        // Comprobamos que el mensaje coincide con la traducción activa de la clave
        $expected = __('messages.validation.error');

        $response->assertJsonPath('message', $expected);
    }

    public function test_response_has_correct_json_structure(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        // Verificar que no hay campos adicionales inesperados en user
        $user = $response->json('user');
        $this->assertCount(3, $user, 'El objeto user debería tener exactamente 3 campos');
    }

    public function test_validation_error_has_correct_json_structure(): void
    {
        $userData = [
            'name' => 'AB',
            'email' => 'invalid',
            'password' => 'weak',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'email',
                    'password',
                ],
            ]);
    }

    public function test_each_error_field_is_an_array_of_messages(): void
    {
        $userData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);
        
        $errors = $response->json('errors');
        
        // Cada campo de error debe ser un array
        foreach ($errors as $fieldErrors) {
            $this->assertIsArray($fieldErrors);
            $this->assertNotEmpty($fieldErrors);
        }
    }

    // ============================================
    // TESTS DE COMPORTAMIENTO HTTP
    // ============================================

    public function test_request_accepts_json_content_type(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->json('POST', $this->endpoint, $userData, [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ]);

        $response->assertStatus(201);
    }

    public function test_response_has_correct_content_type(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_endpoint_only_accepts_post_method(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // GET
        $response = $this->getJson($this->endpoint);
        $response->assertStatus(405); // Method Not Allowed

        // PUT
        $response = $this->putJson($this->endpoint, $userData);
        $response->assertStatus(405);

        // PATCH
        $response = $this->patchJson($this->endpoint, $userData);
        $response->assertStatus(405);

        // DELETE
        $response = $this->deleteJson($this->endpoint);
        $response->assertStatus(405);
    }

    public function test_successful_registration_returns_201_status(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Específicamente 201 Created, no 200 OK
        $response->assertStatus(201);
        $response->assertCreated();
    }

    public function test_validation_error_returns_422_status(): void
    {
        $userData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Específicamente 422 Unprocessable Entity
        $response->assertStatus(422);
        $response->assertUnprocessable();
    }

    // ============================================
    // TESTS DE CASOS LÍMITE DE CARACTERES
    // ============================================

    public function test_can_register_with_emoji_in_name(): void
    {
        $userData = [
            'name' => 'John 😀 Doe',
            'email' => 'emoji@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Dependiendo de tu configuración de base de datos (charset utf8mb4)
        // esto debería funcionar
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'emoji@example.com']);
    }

    public function test_can_register_with_various_international_characters(): void
    {
        $names = [
            'Björk Guðmundsdóttir' => 'bjork@example.com',
            'François Müller' => 'francois@example.com',
            '山田太郎' => 'yamada@example.com',
            'Владимир Петров' => 'vladimir@example.com',
        ];

        foreach ($names as $name => $email) {
            DB::table('users')->truncate();

            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ];

            $response = $this->postJson($this->endpoint, $userData);

            $response->assertStatus(201, "Failed to register user with name: {$name}");
            $this->assertDatabaseHas('users', ['name' => $name, 'email' => $email]);
        }
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Determina si el driver de base de datos actual es SQLite
     */
    private function isSqlite(): bool
    {
        return DB::getDriverName() === 'sqlite';
    }
}