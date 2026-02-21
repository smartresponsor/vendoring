<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Ob;

interface PolicyEngineInterface
{

    public function allow(string $sev): bool;
}

