<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Privacy\Deletion;

interface ReceiptInterface
{

    public function issue(string $vendorId, string $objectId): string;
}
