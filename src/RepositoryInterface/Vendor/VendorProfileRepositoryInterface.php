<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorProfileEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorProfileEntity>
 */
interface VendorProfileRepositoryInterface extends ObjectRepository
{
    public function save(VendorProfileEntity $vendorProfile, bool $flush = false): void;

    public function remove(VendorProfileEntity $vendorProfile, bool $flush = false): void;
}
