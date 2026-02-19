<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Crm;

interface ExporterInterface
{

    public function exportVendor(string $vendorId): string;
}
