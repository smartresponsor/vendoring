<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorDocumentDTO;
use App\Entity\Vendor;
use App\Entity\VendorDocument;

/**
 * Application contract for vendor document service operations.
 */
interface VendorDocumentServiceInterface
{
    /**
     * Executes the upload operation for this runtime surface.
     */
    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument;
}
