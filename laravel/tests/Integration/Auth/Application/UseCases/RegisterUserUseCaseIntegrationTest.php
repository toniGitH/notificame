<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\UseCases;

use Src\Auth\Application\UseCases\RegisterUserUseCase;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Src\Auth\Domain\User\Exceptions\InvalidEmailFormatException;
use Src\Auth\Domain\User\Exceptions\PasswordTooShortException;
use Src\Auth\Domain\User\Exceptions\MissingUserNameException;
use Src\Auth\Domain\User\User;
use Src\Auth\Infrastructure\Persistence\EloquentUserRepository;
use Tests\TestCase;
use App\Models\User as EloquentUser;

/**
 * TEST DE INTEGRACIÓN
 * Testea el RegisterUserUseCase usando repositorio Eloquent y base de datos real.
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

    public function test_throws_exception_when_email_already_exists(): void
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

    public function test_throws_missing_user_name_exception(): void
    {
        $userData = [
            'name' => '',
            'email' => 'john_' . uniqid() . '@example.com',
            'password' => 'Password123!'
        ];

        $this->expectException(MissingUserNameException::class);

        $this->useCase->execute($userData);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_throws_invalid_email_format_exception(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!'
        ];

        $this->expectException(InvalidEmailFormatException::class);

        $this->useCase->execute($userData);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_throws_password_too_short_exception(): void
    {
        $email = 'john_' . uniqid() . '@example.com';
        $userData = [
            'name' => 'John Doe',
            'email' => $email,
            'password' => 'weak'
        ];

        $this->expectException(PasswordTooShortException::class);

        $this->useCase->execute($userData);
        $this->assertDatabaseCount('users', 0);
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

    public function test_password_is_stored_hashed(): void
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
