<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\UseCases;

use InvalidArgumentException;
use Notifier\Auth\Application\Ports\Out\UserRepository;
use Notifier\Auth\Application\UseCases\RegisterUserUseCase;
use Notifier\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * TEST UNITARIO
 * 
 * Testea el RegisterUserUseCase aisladamente usando mocks del repositorio.
 * No toca base de datos, solo verifica la lógica del caso de uso.
 */
final class RegisterUserUseCaseTest extends TestCase
{
    private UserRepository|MockObject $userRepository;
    private RegisterUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear mock del repositorio
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->useCase = new RegisterUserUseCase($this->userRepository);
    }

    public function test_registers_user_with_valid_data(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!'
        ];

        // Configurar el mock: el email no existe
        $this->userRepository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(false);

        // Configurar el mock: se debe llamar a save una vez
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $user = $this->useCase->execute($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name());
        $this->assertEquals('john@example.com', $user->email()->value());
    }

    public function test_throws_exception_when_name_is_missing(): void
    {
        $userData = [
            'email' => 'john@example.com',
            'password' => 'Password123!'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');

        $this->useCase->execute($userData);
    }

    public function test_throws_exception_when_email_is_missing(): void
    {
        $userData = [
            'name' => 'John Doe',
            'password' => 'Password123!'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');

        $this->useCase->execute($userData);
    }

    public function test_throws_exception_when_password_is_missing(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');

        $this->useCase->execute($userData);
    }

    public function test_throws_exception_when_email_already_exists(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!'
        ];

        // Configurar el mock: el email ya existe
        $this->userRepository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        // Save NO debe ser llamado
        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(EmailAlreadyExistsException::class);

        $this->useCase->execute($userData);
    }

    public function test_throws_exception_when_email_format_is_invalid(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!'
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->useCase->execute($userData);
    }

    public function test_throws_exception_when_password_is_too_weak(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak'
        ];

        $this->expectException(InvalidArgumentException::class);

        $this->useCase->execute($userData);
    }

    public function test_calls_repository_save_with_correct_user(): void
    {
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'SecurePass123!'
        ];

        $this->userRepository
            ->method('exists')
            ->willReturn(false);

        /** @var User|null $savedUser */
        $savedUser = null;
        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function(User $user) use (&$savedUser) {
                $savedUser = $user;
            });

        $this->useCase->execute($userData);

        $this->assertNotNull($savedUser);
        $this->assertEquals('Jane Doe', $savedUser->name());
        $this->assertEquals('jane@example.com', $savedUser->email()->value());
    }

    public function test_verifies_email_existence_before_saving(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!'
        ];

        $callOrder = [];

        $this->userRepository
            ->expects($this->once())
            ->method('exists')
            ->willReturnCallback(function() use (&$callOrder) {
                $callOrder[] = 'exists';
                return false;
            });

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function() use (&$callOrder) {
                $callOrder[] = 'save';
            });

        $this->useCase->execute($userData);

        // Verificar que exists se llamó antes que save
        $this->assertEquals(['exists', 'save'], $callOrder);
    }

    public function test_creates_user_with_generated_id(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!'
        ];

        $this->userRepository
            ->method('exists')
            ->willReturn(false);

        $this->userRepository
            ->method('save');

        $user = $this->useCase->execute($userData);

        $this->assertNotEmpty($user->id()->value());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $user->id()->value()
        );
    }
}