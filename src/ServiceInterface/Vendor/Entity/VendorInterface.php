<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Entity;

interface VendorInterface
{

    public function __construct(private string $id, private string $name, private bool $active = true);

    public function id(): string;

    public function name(): string;

    public function active(): bool;
}
