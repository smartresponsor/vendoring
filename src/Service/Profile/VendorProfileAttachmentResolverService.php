<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Profile;

use App\Vendoring\Projection\Vendor\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ServiceInterface\Profile\VendorProfileAttachmentResolverServiceInterface;

/**
 * Safe default profile attachment resolver used when no host-level attachment bridge is installed.
 */
final readonly class VendorProfileAttachmentResolverService implements VendorProfileAttachmentResolverServiceInterface
{
    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        return VendorPublicProfileAttachmentProjection::empty();
    }
}
