<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorAttachment;

final readonly class VendorAttachmentUploadedEvent
{
    public function __construct(public VendorAttachment $attachment)
    {
    }
}
