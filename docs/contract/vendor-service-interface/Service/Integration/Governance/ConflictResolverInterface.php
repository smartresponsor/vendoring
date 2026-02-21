<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Governance;

interface ConflictResolverInterface
{

    public function resolve(array $a, array $b): array;
}

