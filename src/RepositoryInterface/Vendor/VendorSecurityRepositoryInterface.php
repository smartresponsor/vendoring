<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorSecurityEntity;
use App\Vendoring\EntityInterface\Vendor\VendorSecurityEntityInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorSecurityEntity>
 */
interface VendorSecurityRepositoryInterface extends ObjectRepository
{
    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityEntityInterface;

    public function save(VendorSecurityEntity $vendorSecurity, bool $flush = false): void;
}
