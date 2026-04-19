<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorLedgerBinding;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorLedgerBinding>
 */
interface VendorLedgerBindingRepositoryInterface extends ObjectRepository {}
