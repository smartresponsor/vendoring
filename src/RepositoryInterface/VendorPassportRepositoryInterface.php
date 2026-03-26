<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorPassport;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorPassport>
 */
interface VendorPassportRepositoryInterface extends ObjectRepository
{
}
