<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User\ValueObjects;

use Src\Auth\Domain\User\Exceptions\EmptyUserIdException;
use Src\Auth\Domain\User\Exceptions\InvalidUserIdFormatException;
use Src\Auth\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    // Comprueba que se genera un UUID válido con formato correcto (v4)
    public function test_it_generates_valid_uuid(): void
    {
        $userId = UserId::generate();
        
        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $userId->value()
        );
    }

    // Comprueba que cada UUID generado es único
    public function test_it_generates_unique_uuids(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();
        
        $this->assertNotEquals($userId1->value(), $userId2->value());
    }

    // Comprueba que se puede crear un UserId a partir de una cadena UUID válida
    public function test_it_creates_from_valid_uuid_string(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($uuidString);
        
        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertEquals($uuidString, $userId->value());
    }

    // Verifica que lanza excepción si el UUID es completamente inválido
    public function test_it_throws_exception_when_uuid_is_invalid(): void
    {
        $this->expectException(InvalidUserIdFormatException::class);
        $this->expectExceptionMessage('USER_ID_INVALID_FORMAT');
        
        UserId::fromString('invalid-uuid');
    }

    // Verifica que lanza excepción si el formato del UUID está incompleto o mal estructurado
    public function test_it_throws_exception_when_uuid_format_is_wrong(): void
    {
        $this->expectException(InvalidUserIdFormatException::class);
        $this->expectExceptionMessage('USER_ID_INVALID_FORMAT');
        
        UserId::fromString('550e8400-e29b-41d4-a716');
    }

    // Verifica que lanza excepción si el UUID está vacío
    public function test_it_throws_exception_when_uuid_is_empty(): void
    {
        $this->expectException(EmptyUserIdException::class);
        $this->expectExceptionMessage('USER_ID_EMPTY');
        
        UserId::fromString('');
    }

    // Comprueba que dos UserId con el mismo UUID se consideran iguales
    public function test_it_compares_equal_user_ids(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $userId1 = UserId::fromString($uuidString);
        $userId2 = UserId::fromString($uuidString);
        
        $this->assertTrue($userId1->equals($userId2));
    }

    // Comprueba que dos UserId distintos no se consideran iguales
    public function test_it_compares_different_user_ids(): void
    {
        $userId1 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $userId2 = UserId::fromString('660e8400-e29b-41d4-a716-446655440000');
        
        $this->assertFalse($userId1->equals($userId2));
    }

    // Comprueba que un UUID en mayúsculas también es aceptado como válido
    public function test_it_accepts_uppercase_uuid(): void
    {
        $uuidString = '550E8400-E29B-41D4-A716-446655440000'; // UUID válido, debe funcionar
        $userId = UserId::fromString($uuidString);
        
        $this->assertInstanceOf(UserId::class, $userId);
    }

    // Verifica que lanza excepción si el UUID contiene solo espacios
    public function test_it_throws_exception_with_spaces(): void
    {
        $this->expectException(EmptyUserIdException::class);
        
        UserId::fromString('   ');
    }

    // Verifica que lanza excepción si el UUID tiene una versión incorrecta (no v4)
    public function test_it_throws_exception_with_invalid_version(): void
    {
        $this->expectException(InvalidUserIdFormatException::class);
        
        UserId::fromString('550e8400-e29b-11d4-a716-446655440000'); // UUID v1 en lugar de v4
    }

    // Verifica que lanza excepción si el UUID contiene caracteres no válidos
    public function test_it_throws_exception_with_special_characters(): void
    {
        $this->expectException(InvalidUserIdFormatException::class);
        
        UserId::fromString('550e8400-e29b-41d4-a716-44665544000@');
    }

    // Verifica que lanza excepción si el UUID tiene más segmentos de los permitidos
    public function test_it_throws_exception_with_too_many_segments(): void
    {
        $this->expectException(InvalidUserIdFormatException::class);
        
        UserId::fromString('550e8400-e29b-41d4-a716-446655440000-extra');
    }

    // Verifica que lanza excepción si al UUID le faltan los guiones de separación
    public function test_it_throws_exception_with_missing_hyphens(): void
    {
        $this->expectException(InvalidUserIdFormatException::class);
        
        UserId::fromString('550e8400e29b41d4a716446655440000');
    }

    // Comprueba que dos UserId generados aleatoriamente no son iguales
    public function test_equals_returns_false_for_different_generated_ids(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();
        
        $this->assertFalse($userId1->equals($userId2));
    }
}
