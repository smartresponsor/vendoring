<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Ob;

interface AlertBridgeInterface
{

    public function forward(string $sev, string $msg): bool;
}

