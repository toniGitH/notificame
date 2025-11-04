<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User;

use Notifier\Auth\Domain\User\User;
use Notifier\Auth\Domain\User\ValueObjects\UserEmail;
use Notifier\Auth\Domain\User\ValueObjects\UserId;
use Notifier\Auth\Domain\User\ValueObjects\UserPassword;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function test_it_creates_user_with_valid_data(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(User::class, $user);
    }

    public function test_it_generates_id_automatically_on_creation(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(UserId::class, $user->id());
        $this->assertNotEmpty($user->id()->value());
    }

    public function test_it_returns_correct_name(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertEquals($name, $user->name());
    }

    public function test_it_returns_correct_email(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(UserEmail::class, $user->email());
        $this->assertEquals('john@example.com', $user->email()->value());
    }

    public function test_it_returns_correct_password(): void
    {
        $name = 'John Doe';
        $email = UserEmail::fromString('john@example.com');
        $password = UserPassword::fromString('Password123!');
        
        $user = User::create($name, $email, $password);
        
        $this->assertInstanceOf(UserPassword::class, $user->password());
        $this->assertEquals('Password123!', $user->password()->value());
    }

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

    public function test_it_accepts_empty_name(): void
    {
        $user = User::create(
            '',
            UserEmail::fromString('test@example.com'),
            UserPassword::fromString('Password123!')
        );
        
        $this->assertEquals('', $user->name());
    }

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
}