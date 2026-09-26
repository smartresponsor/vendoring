<?php

declare(strict_types=1);

namespace App\Vendoring\Resolver\Profile;

use App\Vendoring\Projection\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ResolverInterface\Profile\VendorProfileAttachmentResolverInterface;

/**
 * Safe fallback used until the host application wires Vendoring to Attaching.
 */
final readonly class VendorNullProfileAttachmentResolver implements VendorProfileAttachmentResolverInterface
{
    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        return VendorPublicProfileAttachmentProjection::empty();
    }
}
