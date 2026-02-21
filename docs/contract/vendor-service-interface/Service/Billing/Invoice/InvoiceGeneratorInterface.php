<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Billing\Invoice;

interface InvoiceGeneratorInterface
{

    public function generate(string $vendorId, array $lines): string;
}

