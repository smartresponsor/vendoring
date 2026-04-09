<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\Entity\Vendor;
use App\Entity\VendorPassport;

interface VendorPassportServiceInterface
{
    public function issue(Vendor $vendor, string $taxId, string $country): VendorPassport;

    public function verify(VendorPassport $passport): VendorPassport;
}
