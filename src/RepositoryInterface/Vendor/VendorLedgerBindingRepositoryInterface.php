<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorLedgerBindingEntity>
 */
interface VendorLedgerBindingRepositoryInterface extends ObjectRepository {}
