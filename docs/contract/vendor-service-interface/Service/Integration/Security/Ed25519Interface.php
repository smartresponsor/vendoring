<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Security;

interface Ed25519Interface
{

    public function ok(string $msg): bool;
}

