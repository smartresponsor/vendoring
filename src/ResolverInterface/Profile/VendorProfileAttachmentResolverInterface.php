<?php

declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Profile;

use App\Vendoring\Projection\VendorPublicProfileAttachmentProjection;

interface VendorProfileAttachmentResolverInterface
{
    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection;
}
