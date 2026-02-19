<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO\Statement;

interface VendorStatementRequestDTOInterface
{

    public function __construct(public string $tenantId, public string $vendorId, public string $from, // Y-m-d public string $to, // Y-m-d public string $currency);
}
