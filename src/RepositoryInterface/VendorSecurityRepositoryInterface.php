<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorSecurity;
use App\Vendoring\EntityInterface\VendorSecurityInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorSecurity>
 */
interface VendorSecurityRepositoryInterface extends ObjectRepository
{
    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityInterface;
}
