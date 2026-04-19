<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorPassport;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorPassport>
 */
interface VendorPassportRepositoryInterface extends ObjectRepository {}
