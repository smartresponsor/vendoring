<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\ValueObject;

interface EmailInterface
{

    public function __construct(private string $value);

    public function value(): string;

    public function __toString(): string;
}
