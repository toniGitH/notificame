<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\UseCases;

use Src\Auth\Application\Ports\Out\UserRepository;
use Src\Auth\Application\UseCases\RegisterUserUseCase;
use Src\Auth\Domain\User\Exceptions\EmailAlreadyExistsException;
use Src\Auth\Domain\User\Exceptions\InvalidEmailFormatException;
use Src\Auth\Domain\User\Exceptions\PasswordTooShortException;
use Src\Auth\Domain\User\User;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->userRepository
            ->expects($this->once())
            ->method('exists')
            ->with($this->isInstanceOf(UserEmail::class))
            ->willReturn(false);

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(User::class));

        $user = $this->useCase->execute($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name());
        $this->assertEquals('john@example.com', $user->email()->value());
    }

    public function test_throws_email_already_exists_and_does_not_save(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'Password123!'
        ];

        $this->userRepository
            ->expects($this->once())
            ->method('exists')
            ->willReturn(true);

        $this->userRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(EmailAlreadyExistsException::class);

        $this->useCase->execute($userData);
    }

    public function test_throws_invalid_email_format_exception_for_bad_email(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'Password123!'
        ];

        $this->userRepository
            ->expects($this->never())
            ->method('exists');

        $this->expectException(InvalidEmailFormatException::class);

        $this->useCase->execute($userData);
    }

    public function test_throws_password_too_short_exception_for_weak_password(): void
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak'
        ];

        $this->userRepository
            ->method('exists')
            ->willReturn(false);

        $this->expectException(PasswordTooShortException::class);

        $this->useCase->execute($userData);
    }

    public function test_calls_repository_save_with_correct_user_and_captures_saved_user(): void
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
            ->willReturnCallback(function (User $user) use (&$savedUser) {
                $savedUser = $user;
            });

        $this->useCase->execute($userData);

        $this->assertNotNull($savedUser);
        $this->assertEquals('Jane Doe', $savedUser->name());
        $this->assertEquals('jane@example.com', $savedUser->email()->value());
    }

    public function test_verifies_email_existence_before_saving_order(): void
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
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'exists';
                return false;
            });

        $this->userRepository
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(function () use (&$callOrder) {
                $callOrder[] = 'save';
            });

        $this->useCase->execute($userData);

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
