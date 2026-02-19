<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\DTO\Vendor\VendorDocumentDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorDocument;

interface VendorDocumentServiceInterface
{
    public function upload(Vendor $vendor, VendorDocumentDTO $dto): VendorDocument;
}
