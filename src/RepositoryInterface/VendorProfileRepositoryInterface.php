<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorProfile;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorProfile>
 */
interface VendorProfileRepositoryInterface extends ObjectRepository
{
    public function save(VendorProfile $vendorProfile, bool $flush = false): void;

    public function remove(VendorProfile $vendorProfile, bool $flush = false): void;
}
