<?php

declare(strict_types=1);

namespace App\Vendoring\Event\Vendor;

use App\Vendoring\Entity\Vendor\VendorMediaEntity;

final readonly class VendorMediaUploadedEvent
{
    public function __construct(public VendorMediaEntity $media) {}
}
