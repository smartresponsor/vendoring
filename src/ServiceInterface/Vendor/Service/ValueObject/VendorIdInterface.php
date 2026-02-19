<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\ValueObject;

interface VendorIdInterface
{

    public function __construct(private string $value);

    public function value(): string;

    public function __toString(): string;
}
