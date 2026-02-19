<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\EntityInterface\Vendor\Entity;

interface VendorPassportInterface
{

    public function __construct(Vendor $vendor, string $taxId, string $country);

    public function markVerified(): void;
}
