<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorPassport;

interface VendorPassportServiceInterface
{
    public function issue(Vendor $vendor, string $taxId, string $country): VendorPassport;

    public function verify(VendorPassport $passport): VendorPassport;
}
