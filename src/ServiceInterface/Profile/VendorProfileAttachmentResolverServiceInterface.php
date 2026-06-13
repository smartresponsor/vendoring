<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Profile;

use App\Vendoring\Projection\Vendor\VendorPublicProfileAttachmentProjection;

interface VendorProfileAttachmentResolverServiceInterface
{
    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection;
}
