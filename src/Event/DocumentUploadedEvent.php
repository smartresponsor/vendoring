<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\VendorDocument;

final readonly class DocumentUploadedEvent
{
    public function __construct(public VendorDocument $document) {}
}
