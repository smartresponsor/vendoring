<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorMediaEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorMediaEntity>
 */
interface VendorMediaRepositoryInterface extends ObjectRepository
{
    public function save(VendorMediaEntity $vendorMedia, bool $flush = false): void;

    public function remove(VendorMediaEntity $vendorMedia, bool $flush = false): void;
}
