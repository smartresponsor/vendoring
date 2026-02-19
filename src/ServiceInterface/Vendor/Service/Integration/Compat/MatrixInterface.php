<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Compat;

interface MatrixInterface
{

    public function supports(string $sys, string $ver, string $feature): bool;
}
