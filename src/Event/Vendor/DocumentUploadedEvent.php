<?php
declare(strict_types = 1);

namespace App\Event\Vendor;

use App\Entity\Vendor\VendorDocument;

final class DocumentUploadedEvent
{
    public function __construct(public readonly VendorDocument $document)
    {
    }
}
