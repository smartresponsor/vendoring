<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorAttachmentEntity;

final readonly class VendorAttachmentUploadedEvent
{
    public function __construct(public VendorAttachmentEntity $attachment) {}
}
