<?php
declare(strict_types=1);

namespace App\Event\Vendor;

use App\Entity\Vendor\VendorAttachment;

final class VendorAttachmentUploadedEvent
{
    public function __construct(public readonly VendorAttachment $attachment) {}
}
