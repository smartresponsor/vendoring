<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorDocument;

final readonly class DocumentUploadedEvent
{
    public function __construct(public VendorDocument $document)
    {
    }
}
