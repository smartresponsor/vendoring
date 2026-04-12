<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\ValueObject;

use InvalidArgumentException;

final readonly class BrandName
{
    private function __construct(private string $value) {}

    public static function fromRaw(string $value): self
    {
        $normalized = trim($value);

        if ('' === $normalized) {
            throw new InvalidArgumentException('brand_name_required');
        }

        if (mb_strlen($normalized) > 255) {
            throw new InvalidArgumentException('brand_name_too_long');
        }

        return new self($normalized);
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
