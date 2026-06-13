<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorDocumentEntity;

final readonly class VendorDocumentUploadedEvent
{
    public function __construct(public VendorDocumentEntity $document) {}
}
