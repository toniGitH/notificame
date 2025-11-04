<?php

declare(strict_types=1);

namespace Tests\Feature\Auth\Infrastructure\Controllers;

use App\Models\User as EloquentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * TEST DE FEATURE (End-to-End)
 * 
 * Testea el RegisterController con el flujo HTTP completo:
 * HTTP Request → Validación → Controller → UseCase → Repository → BD → Response
 * 
 * Este es el ÚNICO tipo de test apropiado para un controller.
 * Prueba la funcionalidad real como lo haría un usuario/cliente HTTP.
 */
final class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/register';

    // ========================================
    // Tests de registro exitoso
    // ========================================

    public function test_registers_user_successfully_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);

        // Verificar que se guardó en la base de datos
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_registered_user_data_matches_request(): void
    {
        $userData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
            ->assertJson([
                'user' => [
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com'
                ]
            ]);
    }

    public function test_registered_user_receives_valid_uuid(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $userId = $response->json('user.id');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $userId
        );
    }

    public function test_password_is_not_returned_in_response(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201)
            ->assertJsonMissing(['password']);
    }

    // ========================================
    // Tests de validación: campos requeridos
    // ========================================

    public function test_fails_when_name_is_missing(): void
    {
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_email_is_missing(): void
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_is_missing(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_confirmation_is_missing(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    // ========================================
    // Tests de validación: formato de datos
    // ========================================

    public function test_fails_when_name_is_too_short(): void
    {
        $userData = [
            'name' => 'Jo',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_email_format_is_invalid(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_email_has_no_domain(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_is_too_short(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Pass1!',
            'password_confirmation' => 'Pass1!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_passwords_do_not_match(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPass123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseCount('users', 0);
    }

    // ========================================
    // Tests de validación: reglas de negocio
    // ========================================

    public function test_fails_when_email_already_exists(): void
    {
        // Crear usuario existente directamente en BD
        EloquentUser::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        // Laravel detecta el duplicado antes que el caso de uso
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Solo debe existir el usuario original
        $this->assertDatabaseCount('users', 1);
    }

    public function test_laravel_validation_catches_duplicate_email(): void
    {
        // Primer registro exitoso
        $firstUserData = [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $this->postJson($this->endpoint, $firstUserData)->assertStatus(201);

        // Segundo intento con mismo email
        // Como el primer usuario se registró correctamente a través del endpoint,
        // la validación 'unique:users,email' de Laravel lo detecta primero
        $secondUserData = [
            'name' => 'Second User',
            'email' => 'duplicate@example.com',
            'password' => 'Password456!',
            'password_confirmation' => 'Password456!'
        ];

        $response = $this->postJson($this->endpoint, $secondUserData);

        // 422 porque la validación de Laravel lo detecta antes del caso de uso
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Solo debe existir el primer usuario
        $this->assertDatabaseCount('users', 1);
    }

    public function test_fails_when_password_has_no_uppercase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123!',
            'password_confirmation' => 'password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_has_no_lowercase(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'PASSWORD123!',
            'password_confirmation' => 'PASSWORD123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_has_no_number(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password!',
            'password_confirmation' => 'Password!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_has_no_special_character(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(422);

        $this->assertDatabaseCount('users', 0);
    }

    // ========================================
    // Tests de casos especiales
    // ========================================

    public function test_accepts_name_with_special_characters(): void
    {
        $userData = [
            'name' => "O'Brien-Smith Jr.",
            'email' => 'obrien@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => "O'Brien-Smith Jr.",
            'email' => 'obrien@example.com'
        ]);
    }

    public function test_accepts_email_with_plus_sign(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test+tag@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'test+tag@example.com'
        ]);
    }

    public function test_accepts_email_with_subdomain(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@mail.example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'test@mail.example.com'
        ]);
    }

    public function test_accepts_password_with_multiple_special_characters(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'P@ssw0rd!#$',
            'password_confirmation' => 'P@ssw0rd!#$'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);
    }

    // ========================================
    // Tests de múltiples registros
    // ========================================

    public function test_allows_multiple_users_to_register(): void
    {
        $user1Data = [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $user2Data = [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'Password456!',
            'password_confirmation' => 'Password456!'
        ];

        $response1 = $this->postJson($this->endpoint, $user1Data);
        $response2 = $this->postJson($this->endpoint, $user2Data);

        $response1->assertStatus(201);
        $response2->assertStatus(201);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_each_user_receives_unique_id(): void
    {
        $user1Data = [
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $user2Data = [
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'Password456!',
            'password_confirmation' => 'Password456!'
        ];

        $response1 = $this->postJson($this->endpoint, $user1Data);
        $response2 = $this->postJson($this->endpoint, $user2Data);

        $userId1 = $response1->json('user.id');
        $userId2 = $response2->json('user.id');

        $this->assertNotEquals($userId1, $userId2);
    }

    // ========================================
    // Tests de integridad de datos
    // ========================================

    public function test_stored_password_is_hashed(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $this->postJson($this->endpoint, $userData);

        $user = EloquentUser::where('email', 'john@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('Password123!', $user->password);
        $this->assertTrue(strlen($user->password) > 50); // Los hashes bcrypt son largos
    }

    public function test_user_can_be_retrieved_after_registration(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        $response = $this->postJson($this->endpoint, $userData);

        $userId = $response->json('user.id');
        $user = EloquentUser::find($userId);

        $this->assertNotNull($user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
}