<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User\ValueObjects;

use InvalidArgumentException;
use Notifier\Auth\Domain\User\ValueObjects\UserPassword;
use PHPUnit\Framework\TestCase;

final class UserPasswordTest extends TestCase
{
    public function test_it_creates_valid_password(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertInstanceOf(UserPassword::class, $password);
        $this->assertEquals('Password123!', $password->value());
    }

    public function test_it_throws_exception_when_password_is_too_short(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must be at least 8 characters long');
        
        UserPassword::fromString('Pass1!');
    }

    public function test_it_throws_exception_when_password_has_no_uppercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one uppercase letter');
        
        UserPassword::fromString('password123!');
    }

    public function test_it_throws_exception_when_password_has_no_lowercase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one lowercase letter');
        
        UserPassword::fromString('PASSWORD123!');
    }

    public function test_it_throws_exception_when_password_has_no_number(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one number');
        
        UserPassword::fromString('Password!');
    }

    public function test_it_throws_exception_when_password_has_no_special_character(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Password must contain at least one special character');
        
        UserPassword::fromString('Password123');
    }

    public function test_it_accepts_password_with_minimum_requirements(): void
    {
        $password = UserPassword::fromString('Abcdef1!');
        
        $this->assertEquals('Abcdef1!', $password->value());
    }

    public function test_it_accepts_password_with_multiple_special_characters(): void
    {
        $password = UserPassword::fromString('Pass@word123#');
        
        $this->assertEquals('Pass@word123#', $password->value());
    }

    public function test_it_compares_equal_passwords(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertTrue($password->equals('Password123!'));
    }

    public function test_it_compares_different_passwords(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertFalse($password->equals('DifferentPass123!'));
    }

    public function test_it_accepts_long_password(): void
    {
        $longPassword = 'VeryLongPassword123!WithManyCharacters';
        $password = UserPassword::fromString($longPassword);
        
        $this->assertEquals($longPassword, $password->value());
    }
}