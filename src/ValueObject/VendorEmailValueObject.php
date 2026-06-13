<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

use InvalidArgumentException;

final readonly class VendorEmailValueObject
{
    public function __construct(private string $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
