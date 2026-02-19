<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Privacy\Consent;

interface AuditInterface
{

    public function record(string $vendorId, string $action): bool;
}
