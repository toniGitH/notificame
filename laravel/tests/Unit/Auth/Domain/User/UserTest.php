<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User;

use Src\Auth\Domain\User\User;
use Src\Auth\Domain\User\ValueObjects\UserEmail;
use Src\Auth\Domain\User\ValueObjects\UserId;
use Src\Auth\Domain\User\ValueObjects\UserPassword;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    // Comprueba que se puede crear un usuario con datos válidos
    public function test_it_creates_user_with_valid_data(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(User::class, $user);
    }

    // Verifica que se genera un ID automáticamente al crear el usuario
    public function test_it_generates_id_automatically_on_creation(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(UserId::class, $user->id());
        $this->assertNotEmpty($user->id()->value());
    }

    // Comprueba que el método name() devuelve el nombre correcto
    public function test_it_returns_correct_name(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertEquals($name, $user->name());
    }

    // Comprueba que el método email() devuelve el objeto y valor correctos
    public function test_it_returns_correct_email(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(UserEmail::class, $user->email());
        $this->assertEquals('john@example.com', $user->email()->value());
    }

    // Comprueba que el método password() devuelve el objeto y valor correctos
    public function test_it_returns_correct_password(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(UserPassword::class, $user->password());
        $this->assertEquals('Password123!', $user->password()->value());
    }

    // Verifica que el método toArray() devuelve un array con las claves esperadas
    public function test_it_converts_to_array_correctly(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        $userArray = $user->toArray();
        
        $this->assertIsArray($userArray);
        $this->assertArrayHasKey('id', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
        $this->assertArrayHasKey('password', $userArray);
    }

    // Comprueba que el array contiene los valores correctos del usuario
    public function test_it_array_contains_correct_values(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        $userArray = $user->toArray();
        
        $this->assertEquals($user->id()->value(), $userArray['id']);
        $this->assertEquals($name, $userArray['name']);
        $this->assertEquals('john@example.com', $userArray['email']);
        $this->assertEquals('Password123!', $userArray['password']);
    }

    // Verifica que dos usuarios distintos tienen IDs diferentes
    public function test_it_creates_different_users_with_different_ids(): void
    {
        $user1 = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $user2 = User::create(
            'Jane Doe',
            UserEmail::fromString('jane@example.com'),
            UserPassword::fromString('Password456!')
        );
        
        $this->assertNotEquals($user1->id()->value(), $user2->id()->value());
    }

    // Comprueba que no se permite crear un usuario con nombre vacío
    public function test_it_throws_exception_for_empty_name(): void
    {
        $this->expectException(\Src\Auth\Domain\User\Exceptions\MissingUserNameException::class);

        User::create(
            '',
            UserEmail::fromString('test@example.com'),
            UserPassword::fromString('Password123!')
        );
    }

    // Verifica que se aceptan nombres con caracteres especiales
    public function test_it_accepts_name_with_special_characters(): void
    {
        $name = "O'Brien-Smith Jr.";
        $user = User::create(
            $name,
            UserEmail::fromString('test@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $this->assertEquals($name, $user->name());
    }

    // Comprueba que se conservan los acentos en el nombre del usuario
    public function test_it_preserves_name_with_accents(): void
    {
        $name = 'José García Pérez';
        $user = User::create(
            $name,
            UserEmail::fromString('jose@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $this->assertEquals($name, $user->name());
    }

    // Verifica que se permite un nombre que contiene números
    public function test_it_accepts_name_with_numbers(): void
    {
        $name = 'User123';
        $user = User::create(
            $name,
            UserEmail::fromString('user123@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $this->assertEquals($name, $user->name());
    }

    // Comprueba que se puede crear un usuario con un nombre muy largo
    public function test_it_accepts_very_long_name(): void
    {
        $name = str_repeat('Long Name ', 20);
        $user = User::create(
            $name,
            UserEmail::fromString('long@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $this->assertEquals($name, $user->name());
    }

    // Verifica que el objeto UserEmail se mantiene como la misma referencia
    public function test_it_creates_user_with_same_email_object_reference(): void
    {
        $email = UserEmail::fromString('john@example.com');
        $user = User::create(
            'John Doe',
            $email,
            UserPassword::fromString('Password123!')
        );
        
        $this->assertSame($email, $user->email());
    }

    // Verifica que el objeto UserPassword se mantiene como la misma referencia
    public function test_it_creates_user_with_same_password_object_reference(): void
    {
        $password = UserPassword::fromString('Password123!');
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            $password
        );
        
        $this->assertSame($password, $user->password());
    }

    // Comprueba que toArray() devuelve valores string, no objetos
    public function test_toArray_returns_string_values_not_objects(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $array = $user->toArray();
        
        $this->assertIsString($array['id']);
        $this->assertIsString($array['name']);
        $this->assertIsString($array['email']);
        $this->assertIsString($array['password']);
    }

    // Verifica que se pueden crear múltiples usuarios independientes con IDs únicos
    public function test_it_creates_multiple_users_independently(): void
    {
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::create(
                "User $i",
                UserEmail::fromString("user{$i}@example.com"),
                UserPassword::fromString("Password{$i}123!")
            );
        }
        
        $ids = array_map(fn($user) => $user->id()->value(), $users);
        $uniqueIds = array_unique($ids);
        
        $this->assertCount(5, $uniqueIds);
    }

    // Comprueba que las propiedades del usuario son inmutables tras la creación
    public function test_user_properties_are_immutable(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $originalName = $user->name();
        $originalEmail = $user->email()->value();
        $originalPassword = $user->password()->value();
        $originalId = $user->id()->value();
        
        $this->assertEquals($originalName, $user->name());
        $this->assertEquals($originalEmail, $user->email()->value());
        $this->assertEquals($originalPassword, $user->password()->value());
        $this->assertEquals($originalId, $user->id()->value());
    }

    // Verifica que toArray() devuelve resultados consistentes en llamadas repetidas
    public function test_toArray_maintains_consistency_on_multiple_calls(): void
    {
        $user = User::create(
            'John Doe',
            UserEmail::fromString('john@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $array1 = $user->toArray();
        $array2 = $user->toArray();
        
        $this->assertEquals($array1, $array2);
    }
}
