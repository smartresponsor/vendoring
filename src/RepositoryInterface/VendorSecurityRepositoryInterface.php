<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\VendorSecurity;
use App\EntityInterface\VendorSecurityInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorSecurity>
 */
interface VendorSecurityRepositoryInterface extends ObjectRepository
{
    public function findOneActiveForVendorId(int $vendorId): ?VendorSecurityInterface;
}
