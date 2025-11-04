<?php

declare(strict_types=1);

namespace Notifier\Auth\Domain\User\ValueObjects;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

/**
 * Value Object para el ID del usuario
 */
final class UserId
{
    private function __construct(
        private string $value
    ) {
        $this->ensureIsValidUuid($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    private function ensureIsValidUuid(string $value): void
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidArgumentException(
                sprintf('<%s> does not allow the value <%s>.', static::class, $value)
            );
        }
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value;
    }
}