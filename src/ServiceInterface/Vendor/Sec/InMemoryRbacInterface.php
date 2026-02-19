<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Sec;

interface InMemoryRbacInterface
{

    public function __construct(private array $matrix);

    public function can(string $actor, string $role, string $res, string $op): bool;
}
