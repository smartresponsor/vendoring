<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface;

use App\Vendoring\Entity\VendorMedia;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorMedia>
 */
interface VendorMediaRepositoryInterface extends ObjectRepository
{
    public function save(VendorMedia $vendorMedia, bool $flush = false): void;

    public function remove(VendorMedia $vendorMedia, bool $flush = false): void;
}
