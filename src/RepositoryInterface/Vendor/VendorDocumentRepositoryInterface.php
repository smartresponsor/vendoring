<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorDocumentEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorDocumentEntity>
 */
interface VendorDocumentRepositoryInterface extends ObjectRepository
{
    public function save(VendorDocumentEntity $vendorDocument, bool $flush = false): void;

    public function remove(VendorDocumentEntity $vendorDocument, bool $flush = false): void;
}
