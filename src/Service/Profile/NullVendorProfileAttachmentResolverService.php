<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\Projection\Vendor\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ServiceInterface\Profile\VendorProfileAttachmentResolverServiceInterface;

/**
 * Safe fallback used until the host application wires Vendoring to Attaching.
 */
final readonly class NullVendorProfileAttachmentResolverService implements VendorProfileAttachmentResolverServiceInterface
{
    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        return VendorPublicProfileAttachmentProjection::empty();
    }
}
