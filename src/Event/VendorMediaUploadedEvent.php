<?php

declare(strict_types=1);

namespace App\Vendoring\Event;

use App\Vendoring\Entity\VendorMedia;

final readonly class VendorMediaUploadedEvent
{
    public function __construct(public VendorMedia $media) {}
}
