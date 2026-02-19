<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\DTO\Vendor\VendorProfileDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorProfile;

interface VendorProfileServiceInterface
{
    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile;
}
