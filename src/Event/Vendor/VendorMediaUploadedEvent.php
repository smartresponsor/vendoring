<?php
declare(strict_types=1);

namespace App\Event\Vendor;

use App\Entity\Vendor\VendorMedia;

final class VendorMediaUploadedEvent
{
    public function __construct(public readonly VendorMedia $media) {}
}
