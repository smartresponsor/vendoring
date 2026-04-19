<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorMedia;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorMedia>
 */
interface VendorMediaRepositoryInterface extends ObjectRepository {}
