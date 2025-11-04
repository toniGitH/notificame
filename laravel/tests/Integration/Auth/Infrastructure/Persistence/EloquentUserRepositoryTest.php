<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructure\Persistence;

use App\Models\User as EloquentUser;
use Illuminate\Database\QueryException;
use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use Notifier\Auth\Domain\User\ValueObjects\UserPassword;
use Notifier\Auth\Infrastructure\Persistence\EloquentUserRepository;
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

    // ========================================
    // Tests del método save()
    // ========================================

    public function test_saves_user_to_database(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $this->assertDatabaseHas('users', [
            'id' => $user->id()->value(),
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_saves_user_with_correct_id(): void
    {
        $user = User::create(
            'Jane Doe',
            UserEmail::fromString('jane@example.com'),
            UserPassword::fromString('Password123!')
        );

        $userId = $user->id()->value();

        $this->repository->save($user);

        $eloquentUser = EloquentUser::find($userId);

        $this->assertNotNull($eloquentUser);
        $this->assertEquals($userId, $eloquentUser->id);
    }

    public function test_saves_user_with_correct_name(): void
    {
        $user = User::create(
            'Test User',
            UserEmail::fromString('test@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::where('email', 'test@example.com')->first();

        $this->assertEquals('Test User', $eloquentUser->name);
    }

    public function test_saves_user_with_correct_email(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::where('name', 'John Doe')->first();

        $this->assertEquals('john@example.com', $eloquentUser->email);
    }

    public function test_saves_user_with_password(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::where('email', 'john@example.com')->first();

        $this->assertNotNull($eloquentUser->password);
        $this->assertNotEmpty($eloquentUser->password);
        // Verificar que la contraseña se hasheó (el cast 'hashed' del modelo)
        $this->assertNotEquals('Password123!', $eloquentUser->password);
    }

    public function test_saves_multiple_users(): void
    {
        $user1 = User::create(
            'User One',
            UserEmail::fromString('user1@example.com'),
            UserPassword::fromString('Password123!')
        );

        $user2 = User::create(
            'User Two',
            UserEmail::fromString('user2@example.com'),
            UserPassword::fromString('Password456!')
        );

        $this->repository->save($user1);
        $this->repository->save($user2);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['email' => 'user1@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'user2@example.com']);
    }

    public function test_saves_user_with_special_characters_in_name(): void
    {
        $user = User::create(
            "O'Brien-Smith Jr.",
            UserEmail::fromString('obrien@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::where('email', 'obrien@example.com')->first();

        $this->assertEquals("O'Brien-Smith Jr.", $eloquentUser->name);
    }

    public function test_saves_user_with_email_containing_plus_sign(): void
    {
        $user = User::create(
            'Test User',
            UserEmail::fromString('test+tag@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $this->assertDatabaseHas('users', [
            'email' => 'test+tag@example.com'
        ]);
    }

    public function test_saves_user_with_empty_name(): void
    {
        $user = User::create(
            '',
            UserEmail::fromString('test@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::where('email', 'test@example.com')->first();

        $this->assertEquals('', $eloquentUser->name);
    }

    public function test_saved_user_timestamps_are_set(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::find($user->id()->value());

        $this->assertNotNull($eloquentUser->created_at);
        $this->assertNotNull($eloquentUser->updated_at);
    }

    public function test_saved_user_id_is_valid_uuid(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );

        $this->repository->save($user);

        $eloquentUser = EloquentUser::where('email', 'john@example.com')->first();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $eloquentUser->id
        );
    }

    public function test_saving_duplicate_email_throws_query_exception(): void
    {
        $user1 = User::create(
            'First User',
            UserEmail::fromString('duplicate@example.com'),
            UserPassword::fromString('Password123!')
        );

        $user2 = User::create(
            'Second User',
            UserEmail::fromString('duplicate@example.com'),
            UserPassword::fromString('Password456!')
        );

        $this->repository->save($user1);

        $this->expectException(QueryException::class);
        $this->repository->save($user2);
    }

    // ========================================
    // Tests del método exists()
    // ========================================

    public function test_exists_returns_true_when_email_exists(): void
    {
        EloquentUser::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $email = UserEmail::fromString('existing@example.com');
        $exists = $this->repository->exists($email);

        $this->assertTrue($exists);
    }

    public function test_exists_returns_false_when_email_does_not_exist(): void
    {
        $email = UserEmail::fromString('nonexistent@example.com');
        $exists = $this->repository->exists($email);

        $this->assertFalse($exists);
    }

    public function test_exists_is_case_insensitive_for_email(): void
    {
        // Este test verifica comportamiento específico de MySQL (case-insensitive)
        // SQLite usado en tests es case-sensitive por defecto
        // Valorar lanzar este test contra una base de datos MySQL
        // Lanzar este test contra MySQL puede provocar la caída del contenedor Laravel
        $this->markTestSkipped(
            'SQLite is case-sensitive by default. ' .
            'In production (MySQL), emails are case-insensitive. ' .
            'Consider normalizing emails to lowercase in UserEmail VO.'
        );
    }

    // Este es el contenido del anterior test, que se ha omitido 
    /* public function test_exists_is_case_insensitive_for_email(): void
    {
        EloquentUser::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $lowerEmail = UserEmail::fromString('test@example.com');
        $upperEmail = UserEmail::fromString('TEST@EXAMPLE.COM');

        $this->assertTrue($this->repository->exists($lowerEmail));
        // MySQL por defecto es case-insensitive para emails
        $this->assertTrue($this->repository->exists($upperEmail));
    } */

    public function test_exists_after_save(): void
    {
        $email = UserEmail::fromString('test@example.com');
        
        // Verificar que no existe antes
        $this->assertFalse($this->repository->exists($email));

        // Guardar usuario
        $user = User::create(
            'Test User',
            $email,
            UserPassword::fromString('Password123!')
        );
        $this->repository->save($user);

        // Verificar que ahora existe
        $this->assertTrue($this->repository->exists($email));
    }

    public function test_exists_with_email_containing_plus_sign(): void
    {
        EloquentUser::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Test User',
            'email' => 'test+tag@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $email = UserEmail::fromString('test+tag@example.com');
        
        $this->assertTrue($this->repository->exists($email));
    }

    public function test_exists_with_subdomain_email(): void
    {
        EloquentUser::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Test User',
            'email' => 'test@mail.example.com',
            'password' => bcrypt('Password123!')
        ]);

        $email = UserEmail::fromString('test@mail.example.com');
        
        $this->assertTrue($this->repository->exists($email));
    }

    // ========================================
    // Tests de integración entre save() y exists()
    // ========================================

    public function test_multiple_saves_with_different_emails_all_exist(): void
    {
        $user1 = User::create(
            'User One',
            UserEmail::fromString('user1@example.com'),
            UserPassword::fromString('Password123!')
        );

        $user2 = User::create(
            'User Two',
            UserEmail::fromString('user2@example.com'),
            UserPassword::fromString('Password456!')
        );

        $this->repository->save($user1);
        $this->repository->save($user2);

        $this->assertTrue($this->repository->exists(UserEmail::fromString('user1@example.com')));
        $this->assertTrue($this->repository->exists(UserEmail::fromString('user2@example.com')));
        $this->assertFalse($this->repository->exists(UserEmail::fromString('user3@example.com')));
    }

    public function test_repository_preserves_data_integrity(): void
    {
        $originalName = 'John Doe';
        $originalEmail = 'john@example.com';
        $originalPassword = 'Password123!';
        
        $user = User::create(
            $originalName,
            UserEmail::fromString($originalEmail),
            UserPassword::fromString($originalPassword)
        );

        $userId = $user->id()->value();
        
        $this->repository->save($user);

        // Verificar que los datos se guardaron correctamente
        $eloquentUser = EloquentUser::find($userId);
        
        $this->assertEquals($userId, $eloquentUser->id);
        $this->assertEquals($originalName, $eloquentUser->name);
        $this->assertEquals($originalEmail, $eloquentUser->email);
        $this->assertNotEmpty($eloquentUser->password);
        
        // Verificar que exists funciona con el email guardado
        $this->assertTrue($this->repository->exists(UserEmail::fromString($originalEmail)));
    }
}