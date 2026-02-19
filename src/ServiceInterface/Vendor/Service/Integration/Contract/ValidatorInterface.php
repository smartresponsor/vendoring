<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Contract;

interface ValidatorInterface
{

    public function ok(array $schema, array $payload): bool;
}
