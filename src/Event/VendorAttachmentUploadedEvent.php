<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\VendorAttachment;

final readonly class VendorAttachmentUploadedEvent
{
    public function __construct(public VendorAttachment $attachment) {}
}
