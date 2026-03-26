<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorAttachment;

final class VendorAttachmentUploadedEvent
{
    public function __construct(public readonly VendorAttachment $attachment)
    {
    }
}
