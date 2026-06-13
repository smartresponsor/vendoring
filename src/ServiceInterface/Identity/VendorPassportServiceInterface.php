<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Identity;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorPassportEntity;

interface VendorPassportServiceInterface
{
    public function issue(VendorEntity $vendor, string $taxId, string $country): VendorPassportEntity;

    public function verify(VendorPassportEntity $passport): VendorPassportEntity;
}
