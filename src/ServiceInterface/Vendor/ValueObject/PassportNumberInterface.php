<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\ValueObject;

interface PassportNumberInterface
{

    public function __construct(private string $v);

    public function __toString(): string;
}
