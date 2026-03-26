<?php

declare(strict_types=1);

namespace App\Event;

use App\Entity\VendorMedia;

final class VendorMediaUploadedEvent
{
    public function __construct(public readonly VendorMedia $media)
    {
    }
}
