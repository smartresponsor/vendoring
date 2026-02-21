<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Op\Tenant;

interface QuotaInterface
{

    public function allow(string $tenantId, int $currentRps, int $limit): bool;
}

