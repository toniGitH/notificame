<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\InvalidEmailFormatException;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use PHPUnit\Framework\TestCase;

final class UserEmailTest extends TestCase
{
    // Verifica que se crea correctamente un email válido
    public function test_it_creates_valid_email(): void
    {
        $email = UserEmail::fromString('test@example.com');
        
        $this->assertInstanceOf(UserEmail::class, $email);
        $this->assertEquals('test@example.com', $email->value());
    }

    // Verifica que lanza excepción si falta el punto en el dominio
    public function test_it_throws_exception_when_email_has_no_dot(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        $this->expectExceptionMessage('INVALID_EMAIL_FORMAT');
        
        UserEmail::fromString('test@examplecom');
    }

    // Verifica que lanza excepción con formato de email completamente inválido
    public function test_it_throws_exception_when_email_format_is_invalid(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        $this->expectExceptionMessage('INVALID_EMAIL_FORMAT');
        
        UserEmail::fromString('invalid-email.com');
    }

    // Verifica que lanza excepción si no contiene el símbolo '@'
    public function test_it_throws_exception_when_email_has_no_at_symbol(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        $this->expectExceptionMessage('INVALID_EMAIL_FORMAT');
        
        UserEmail::fromString('test.example.com');
    }

    // Verifica que acepta emails con subdominios
    public function test_it_accepts_email_with_subdomain(): void
    {
        $email = UserEmail::fromString('test@mail.example.com');
        
        $this->assertEquals('test@mail.example.com', $email->value());
    }

    // Verifica que acepta emails con signo '+' en la parte local
    public function test_it_accepts_email_with_plus_sign(): void
    {
        $email = UserEmail::fromString('test+tag@example.com');
        
        $this->assertEquals('test+tag@example.com', $email->value());
    }

    // Verifica que dos emails iguales son considerados iguales
    public function test_it_compares_two_equal_emails(): void
    {
        $email1 = UserEmail::fromString('test@example.com');
        $email2 = UserEmail::fromString('test@example.com');
        
        $this->assertTrue($email1->equals($email2));
    }

    // Verifica que dos emails distintos no son considerados iguales
    public function test_it_compares_two_different_emails(): void
    {
        $email1 = UserEmail::fromString('test1@example.com');
        $email2 = UserEmail::fromString('test2@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }

    // Verifica que lanza excepción con cadena vacía
    public function test_it_throws_exception_with_empty_string(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('');
    }

    // Verifica que lanza excepción con solo espacios
    public function test_it_throws_exception_with_spaces_only(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('   ');
    }

    // Verifica que lanza excepción con múltiples símbolos '@'
    public function test_it_throws_exception_with_multiple_at_symbols(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('test@@example.com');
    }

    // Verifica que acepta emails con números en la parte local
    public function test_it_accepts_email_with_numbers(): void
    {
        $email = UserEmail::fromString('user123@example.com');
        
        $this->assertEquals('user123@example.com', $email->value());
    }

    // Verifica que acepta emails con guiones en la parte local
    public function test_it_accepts_email_with_hyphens(): void
    {
        $email = UserEmail::fromString('first-last@example.com');
        
        $this->assertEquals('first-last@example.com', $email->value());
    }

    // Verifica que acepta emails con guiones bajos en la parte local
    public function test_it_accepts_email_with_underscores(): void
    {
        $email = UserEmail::fromString('first_last@example.com');
        
        $this->assertEquals('first_last@example.com', $email->value());
    }

    // Verifica que acepta emails con puntos en la parte local
    public function test_it_accepts_email_with_dots_in_local_part(): void
    {
        $email = UserEmail::fromString('first.last@example.com');
        
        $this->assertEquals('first.last@example.com', $email->value());
    }

    // Verifica que lanza excepción si falta el punto en el dominio
    public function test_it_throws_exception_when_dot_missing_in_domain(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('test@example');
    }

    // Verifica que lanza excepción si el dominio contiene caracteres especiales
    public function test_it_throws_exception_with_special_chars_in_domain(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('test@exam!ple.com');
    }

    // Verifica que acepta emails con varios niveles de subdominios
    public function test_it_accepts_email_with_multiple_subdomain_levels(): void
    {
        $email = UserEmail::fromString('test@mail.internal.example.com');
        
        $this->assertEquals('test@mail.internal.example.com', $email->value());
    }

    // Verifica que lanza excepción si el email empieza con '@'
    public function test_it_throws_exception_starting_with_at(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('@example.com');
    }

    // Verifica que lanza excepción si el email termina con '@'
    public function test_it_throws_exception_ending_with_at(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('test@');
    }

    // Verifica que lanza excepción si el email contiene espacios
    public function test_it_throws_exception_with_spaces_in_email(): void
    {
        $this->expectException(InvalidEmailFormatException::class);
        
        UserEmail::fromString('test user@example.com');
    }

    // Verifica que acepta emails largos válidos
    public function test_it_accepts_long_email(): void
    {
        $email = UserEmail::fromString('verylongemailaddress@verylongdomainname.com');
        
        $this->assertEquals('verylongemailaddress@verylongdomainname.com', $email->value());
    }

    // Verifica que la comparación de emails distingue mayúsculas de minúsculas
    public function test_equals_is_case_sensitive(): void
    {
        $email1 = UserEmail::fromString('Test@Example.com');
        $email2 = UserEmail::fromString('test@example.com');
        
        $this->assertFalse($email1->equals($email2));
    }
}
