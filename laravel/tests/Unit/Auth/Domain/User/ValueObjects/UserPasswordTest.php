<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\PasswordTooShortException;
use Src\Auth\Domain\User\Exceptions\PasswordMissingUppercaseException;
use Src\Auth\Domain\User\Exceptions\PasswordMissingLowercaseException;
use Src\Auth\Domain\User\Exceptions\PasswordMissingNumberException;
use Src\Auth\Domain\User\Exceptions\PasswordMissingSpecialCharacterException;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use PHPUnit\Framework\TestCase;

final class UserPasswordTest extends TestCase
{
    // Verifica que se crea correctamente una contraseña válida
    public function test_it_creates_valid_password(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertInstanceOf(UserPassword::class, $password);
        $this->assertEquals('Password123!', $password->value());
    }

    // Verifica que lanza excepción si la contraseña es demasiado corta
    public function test_it_throws_exception_when_password_is_too_short(): void
    {
        $this->expectException(PasswordTooShortException::class);
        $this->expectExceptionMessage('PASSWORD_TOO_SHORT');
        
        UserPassword::fromString('Pass1!');
    }

    // Verifica que lanza excepción si no tiene mayúsculas
    public function test_it_throws_exception_when_password_has_no_uppercase(): void
    {
        $this->expectException(PasswordMissingUppercaseException::class);
        $this->expectExceptionMessage('PASSWORD_MISSING_UPPERCASE');
        
        UserPassword::fromString('password123!');
    }

    // Verifica que lanza excepción si no tiene minúsculas
    public function test_it_throws_exception_when_password_has_no_lowercase(): void
    {
        $this->expectException(PasswordMissingLowercaseException::class);
        $this->expectExceptionMessage('PASSWORD_MISSING_LOWERCASE');
        
        UserPassword::fromString('PASSWORD123!');
    }

    // Verifica que lanza excepción si no tiene números
    public function test_it_throws_exception_when_password_has_no_number(): void
    {
        $this->expectException(PasswordMissingNumberException::class);
        $this->expectExceptionMessage('PASSWORD_MISSING_NUMBER');
        
        UserPassword::fromString('Password!');
    }

    // Verifica que lanza excepción si no tiene caracteres especiales
    public function test_it_throws_exception_when_password_has_no_special_character(): void
    {
        $this->expectException(PasswordMissingSpecialCharacterException::class);
        $this->expectExceptionMessage('PASSWORD_MISSING_SPECIAL_CHARACTER');
        
        UserPassword::fromString('Password123');
    }

    // Verifica que acepta contraseñas con los requisitos mínimos
    public function test_it_accepts_password_with_minimum_requirements(): void
    {
        $password = UserPassword::fromString('Abcdef1!');
        
        $this->assertEquals('Abcdef1!', $password->value());
    }

    // Verifica que acepta contraseñas con varios caracteres especiales
    public function test_it_accepts_password_with_multiple_special_characters(): void
    {
        $password = UserPassword::fromString('Pass@word123#');
        
        $this->assertEquals('Pass@word123#', $password->value());
    }

    // Verifica que dos contraseñas iguales son consideradas iguales
    public function test_it_compares_equal_passwords(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertTrue($password->equals('Password123!'));
    }

    // Verifica que dos contraseñas diferentes no son iguales
    public function test_it_compares_different_passwords(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertFalse($password->equals('DifferentPass123!'));
    }

    // Verifica que acepta contraseñas largas válidas
    public function test_it_accepts_long_password(): void
    {
        $longPassword = 'VeryLongPassword123!WithManyCharacters';
        $password = UserPassword::fromString($longPassword);
        
        $this->assertEquals($longPassword, $password->value());
    }

    // Verifica que lanza excepción si tiene exactamente 7 caracteres
    public function test_it_throws_exception_with_exactly_7_characters(): void
    {
        $this->expectException(PasswordTooShortException::class);
        
        UserPassword::fromString('Pass12!');
    }

    // Verifica que acepta contraseñas de exactamente 8 caracteres
    public function test_it_accepts_exactly_8_characters(): void
    {
        $password = UserPassword::fromString('Passw1!d');
        
        $this->assertEquals('Passw1!d', $password->value());
    }

    // Verifica que acepta contraseñas con el símbolo '@'
    public function test_it_accepts_password_with_at_symbol(): void
    {
        $password = UserPassword::fromString('P@ssword123');
        
        $this->assertEquals('P@ssword123', $password->value());
    }

    // Verifica que acepta contraseñas con el símbolo '#'
    public function test_it_accepts_password_with_hash_symbol(): void
    {
        $password = UserPassword::fromString('P#ssword123');
        
        $this->assertEquals('P#ssword123', $password->value());
    }

    // Verifica que acepta contraseñas con el símbolo '$'
    public function test_it_accepts_password_with_dollar_symbol(): void
    {
        $password = UserPassword::fromString('P$ssword123');
        
        $this->assertEquals('P$ssword123', $password->value());
    }

    // Verifica que acepta contraseñas con el símbolo '%'
    public function test_it_accepts_password_with_percent_symbol(): void
    {
        $password = UserPassword::fromString('P%ssword123');
        
        $this->assertEquals('P%ssword123', $password->value());
    }

    // Verifica que acepta contraseñas con el símbolo '&'
    public function test_it_accepts_password_with_ampersand_symbol(): void
    {
        $password = UserPassword::fromString('P&ssword123');
        
        $this->assertEquals('P&ssword123', $password->value());
    }

    // Verifica que acepta contraseñas con el símbolo '*'
    public function test_it_accepts_password_with_asterisk_symbol(): void
    {
        $password = UserPassword::fromString('P*ssword123');
        
        $this->assertEquals('P*ssword123', $password->value());
    }

    // Verifica que acepta contraseñas con guion bajo
    public function test_it_accepts_password_with_underscore(): void
    {
        $password = UserPassword::fromString('Pass_word123');
        
        $this->assertEquals('Pass_word123', $password->value());
    }

    // Verifica que acepta contraseñas con guion medio
    public function test_it_accepts_password_with_hyphen(): void
    {
        $password = UserPassword::fromString('Pass-word123');
        
        $this->assertEquals('Pass-word123', $password->value());
    }

    // Verifica que acepta contraseñas con varios números
    public function test_it_accepts_password_with_multiple_numbers(): void
    {
        $password = UserPassword::fromString('Password123456!');
        
        $this->assertEquals('Password123456!', $password->value());
    }

    // Verifica que acepta contraseñas con varias mayúsculas
    public function test_it_accepts_password_with_multiple_uppercase(): void
    {
        $password = UserPassword::fromString('PASSword123!');
        
        $this->assertEquals('PASSword123!', $password->value());
    }

    // Verifica que acepta contraseñas con varias minúsculas
    public function test_it_accepts_password_with_multiple_lowercase(): void
    {
        $password = UserPassword::fromString('Passworddd123!');
        
        $this->assertEquals('Passworddd123!', $password->value());
    }

    // Verifica que lanza excepción si solo tiene minúsculas y números
    public function test_it_throws_exception_with_only_lowercase_and_numbers(): void
    {
        $this->expectException(PasswordMissingUppercaseException::class);
        
        UserPassword::fromString('password123!');
    }

    // Verifica que lanza excepción si solo tiene mayúsculas y números
    public function test_it_throws_exception_with_only_uppercase_and_numbers(): void
    {
        $this->expectException(PasswordMissingLowercaseException::class);
        
        UserPassword::fromString('PASSWORD123!');
    }

    // Verifica que lanza excepción si solo tiene letras y caracteres especiales
    public function test_it_throws_exception_with_only_letters_and_specials(): void
    {
        $this->expectException(PasswordMissingNumberException::class);
        
        UserPassword::fromString('Password!@#');
    }

    // Verifica que la comparación distingue mayúsculas y minúsculas
    public function test_equals_is_case_sensitive(): void
    {
        $password = UserPassword::fromString('Password123!');
        
        $this->assertFalse($password->equals('password123!'));
    }

    // Verifica que acepta contraseñas con espacios
    public function test_it_accepts_password_with_spaces(): void
    {
        $password = UserPassword::fromString('Pass word 123!');
        
        $this->assertEquals('Pass word 123!', $password->value());
    }

    // Verifica que lanza excepción con cadena vacía
    public function test_it_throws_exception_with_empty_string(): void
    {
        $this->expectException(PasswordTooShortException::class);
        
        UserPassword::fromString('');
    }

    // Verifica que acepta contraseñas extremadamente largas
    public function test_it_accepts_very_long_password(): void
    {
        $veryLongPassword = str_repeat('A', 50) . 'a1!';
        $password = UserPassword::fromString($veryLongPassword);
        
        $this->assertEquals($veryLongPassword, $password->value());
    }
}
