<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface;

use App\Entity\VendorMedia;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorMedia>
 */
interface VendorMediaRepositoryInterface extends ObjectRepository {}
