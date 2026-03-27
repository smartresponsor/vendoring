<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorDocument;

final class DocumentUploadedEvent
{
    public function __construct(public readonly VendorDocument $document)
    {
    }
}
