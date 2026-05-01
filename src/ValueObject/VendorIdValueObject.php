<?php

declare(strict_types=1);

namespace App\Vendoring\ValueObject;

use InvalidArgumentException;

final readonly class VendorIdValueObject
{
    public function __construct(private string $value)
    {
        if ('' === $value) {
            throw new InvalidArgumentException('Empty VendorIdValueObject');
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
