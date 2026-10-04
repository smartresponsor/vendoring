<?php

declare(strict_types=1);

namespace App\Vendoring\Resolver\Profile;

use App\Vendoring\Projection\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ResolverInterface\Profile\VendorProfileAttachmentResolverInterface;

/**
 * Safe default profile attachment resolver used when no host-level attachment bridge is installed.
 */
final readonly class VendorProfileAttachmentResolver implements VendorProfileAttachmentResolverInterface
{
    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        return VendorPublicProfileAttachmentProjection::empty();
    }
}
