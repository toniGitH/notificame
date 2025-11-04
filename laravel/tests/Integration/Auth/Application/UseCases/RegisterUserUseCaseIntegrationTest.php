<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\UseCases;

use InvalidArgumentException;
use Notifier\Auth\Application\UseCases\RegisterUserUseCase;
use Notifier\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Tests\TestCase;
use App\Models\User as EloquentUser;

/**
 * TEST DE INTEGRACIÓN
 * 
 * Testea el RegisterUserUseCase con el repositorio real y base de datos.
 * Verifica que el caso de uso funciona correctamente con la infraestructura real.
 */
final class RegisterUserUseCaseIntegrationTest extends TestCase
{
    private RegisterUserUseCase $useCase;
    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentUserRepository();
        $this->useCase = new RegisterUserUseCase($this->repository);
    }

    public function test_registers_user_and_persists_to_database(): void
    {
        $email = 'john_' . uniqid() . '@example.com';
        $userData = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'Password123!'
        ];

        $user = $this->useCase->execute($userData);

        $this->assertInstanceOf(User::class, $user);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name' => 'John Doe'
        ]);
    }

    public function test_throws_exception_when_email_already_exists_in_database(): void
    {
        $email = 'existing_' . uniqid() . '@example.com';
        EloquentUser::create([
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'name' => 'Existing User',
            'email' => $email,
            'password' => bcrypt('Password123!')
        ]);

        $userData = [
            'name' => 'New User',
            'email' => $email,
            'password' => 'Password123!'
        ];

        $this->expectException(EmailAlreadyExistsException::class);

        $this->useCase->execute($userData);
    }

    public function test_saved_user_has_valid_uuid(): void
    {
        $email = 'jane_' . uniqid() . '@example.com';
        $userData = [
            'name' => 'Jane Doe',
            'email' => $email,
            'password' => 'Password123!'
        ];

        $user = $this->useCase->execute($userData);

        $eloquentUser = EloquentUser::where('email', $email)->first();

        $this->assertNotNull($eloquentUser);
        $this->assertEquals($user->id()->value(), $eloquentUser->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $eloquentUser->id
        );
    }

    public function test_password_is_stored_in_database(): void
    {
        $email = 'john_' . uniqid() . '@example.com';
        $userData = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'Password123!'
        ];

        $this->useCase->execute($userData);

        $eloquentUser = EloquentUser::where('email', $email)->first();

        $this->assertNotNull($eloquentUser);
        $this->assertNotEmpty($eloquentUser->password);
    }

    public function test_multiple_users_can_be_registered(): void
    {
        $userData1 = [
            'name' => 'User One',
            'email' => 'user1_' . uniqid() . '@example.com',
            'password' => 'Password123!'
        ];

        $userData2 = [
            'name' => 'User Two',
            'email' => 'user2_' . uniqid() . '@example.com',
            'password' => 'Password456!'
        ];

        $user1 = $this->useCase->execute($userData1);
        $user2 = $this->useCase->execute($userData2);

        $this->assertNotEquals($user1->id()->value(), $user2->id()->value());
        $this->assertDatabaseCount('users', 2);
    }

    public function test_throws_exception_for_missing_required_fields(): void
    {
        $userData = [
            'email' => 'john_' . uniqid() . '@example.com',
            'password' => 'Password123!'
            // falta 'name'
        ];

        // ⚠️ Si el Request valida antes, este test podría lanzar ValidationException en lugar de InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');

        $this->useCase->execute($userData);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_throws_exception_for_invalid_email_format(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!'
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->useCase->execute($userData);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_throws_exception_for_weak_password(): void
    {
        $email = 'john_' . uniqid() . '@example.com';
        $userData = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'weak'
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->useCase->execute($userData);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_data_matches_after_registration(): void
    {
        $email = 'john_' . uniqid() . '@example.com';
        $userData = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'Password123!'
        ];

        $user = $this->useCase->execute($userData);
        $eloquentUser = EloquentUser::find($user->id()->value());

        $this->assertEquals($user->name(), $eloquentUser->name);
        $this->assertEquals($user->email()->value(), $eloquentUser->email);
        $this->assertEquals($user->id()->value(), $eloquentUser->id);
    }

    public function test_special_characters_in_name_are_preserved(): void
    {
        $email = 'obrien_' . uniqid() . '@example.com';
        $userData = [
            'name' => "O'Brien-Smith Jr.",
            'email' => $email,
            'password' => 'Password123!'
        ];

        $user = $this->useCase->execute($userData);
        $eloquentUser = EloquentUser::find($user->id()->value());

        $this->assertEquals("O'Brien-Smith Jr.", $eloquentUser->name);
    }
}
