<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User\ValueObjects;

use InvalidArgumentException;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use PHPUnit\Framework\TestCase;

final class UserEmailTest extends TestCase
{
    public function test_it_creates_valid_email(): void
    {
        $email = UserEmail::fromString('test@example.com');
        
        $this->assertInstanceOf(UserEmail::class, $email);
        $this->assertEquals('test@example.com', $email->value());
    }

    public function test_it_throws_exception_when_email_has_no_dot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        UserEmail::fromString('test@examplecom');
    }

    public function test_it_throws_exception_when_email_format_is_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        UserEmail::fromString('invalid-email.com');
    }

    public function test_it_throws_exception_when_email_has_no_at_symbol(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        UserEmail::fromString('test.example.com');
    }

    public function test_it_accepts_email_with_subdomain(): void
    {
        $email = UserEmail::fromString('test@mail.example.com');
        
        $this->assertEquals('test@mail.example.com', $email->value());
    }

    public function test_it_accepts_email_with_plus_sign(): void
    {
        $email = UserEmail::fromString('test+tag@example.com');
        
        $this->assertEquals('test+tag@example.com', $email->value());
    }

    public function test_it_compares_two_equal_emails(): void
    {
        $email1 = UserEmail::fromString('test@example.com');
        $email2 = UserEmail::fromString('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }

    public function test_it_compares_two_different_emails(): void
    {
        $email1 = UserEmail::fromString('test1@example.com');
        $email2 = UserEmail::fromString('test2@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }
}