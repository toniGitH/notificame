<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\User\ValueObjects;

use InvalidArgumentException;
use Notifier\Auth\Domain\User\ValueObjects\UserId;
use PHPUnit\Framework\TestCase;

final class UserIdTest extends TestCase
{
    public function test_it_generates_valid_uuid(): void
    {
        $userId = UserId::generate();
        
        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $userId->value()
        );
    }

    public function test_it_generates_unique_uuids(): void
    {
        $userId1 = UserId::generate();
        $userId2 = UserId::generate();
        
        $this->assertNotEquals($userId1->value(), $userId2->value());
    }

    public function test_it_creates_from_valid_uuid_string(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $userId = UserId::fromString($uuidString);
        
        $this->assertInstanceOf(UserId::class, $userId);
        $this->assertEquals($uuidString, $userId->value());
    }

    public function test_it_throws_exception_when_uuid_is_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        UserId::fromString('invalid-uuid');
    }

    public function test_it_throws_exception_when_uuid_format_is_wrong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        UserId::fromString('550e8400-e29b-41d4-a716');
    }

    public function test_it_throws_exception_when_uuid_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        UserId::fromString('');
    }

    public function test_it_compares_equal_user_ids(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';
        $userId1 = UserId::fromString($uuidString);
        $userId2 = UserId::fromString($uuidString);
        
        $this->assertTrue($userId1->equals($userId2));
    }

    public function test_it_compares_different_user_ids(): void
    {
        $userId1 = UserId::fromString('550e8400-e29b-41d4-a716-446655440000');
        $userId2 = UserId::fromString('660e8400-e29b-41d4-a716-446655440000');
        
        $this->assertFalse($userId1->equals($userId2));
    }

    public function test_it_accepts_uppercase_uuid(): void
    {
        $uuidString = '550E8400-E29B-41D4-A716-446655440000';
        $userId = UserId::fromString($uuidString);
        
        $this->assertEquals(strtolower($uuidString), strtolower($userId->value()));
    }
}