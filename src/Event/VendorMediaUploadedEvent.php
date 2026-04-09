<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorMedia;

final readonly class VendorMediaUploadedEvent
{
    public function __construct(public VendorMedia $media)
    {
    }
}
