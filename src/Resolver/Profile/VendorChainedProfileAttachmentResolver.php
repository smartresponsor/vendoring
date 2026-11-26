<?php

declare(strict_types=1);

namespace App\Vendoring\Resolver\Profile;

use App\Vendoring\Projection\VendorPublicProfileAttachmentProjection;
use App\Vendoring\ResolverInterface\Profile\VendorProfileAttachmentResolverInterface;

/**
 * Resolves canonical Attaching media first and falls back to legacy Vendoring path fields.
 */
final readonly class VendorChainedProfileAttachmentResolver implements VendorProfileAttachmentResolverInterface
{
    public function __construct(
        private VendorProfileAttachmentResolverInterface $primaryResolver,
        private VendorProfileAttachmentResolverInterface $fallbackResolver,
    ) {
    }

    public function resolvePrimaryForVendorSlot(int $vendorId, string $slot): VendorPublicProfileAttachmentProjection
    {
        $primary = $this->primaryResolver->resolvePrimaryForVendorSlot($vendorId, $slot);

        if (null !== $primary->attachmentId || null !== $primary->url) {
            return $primary;
        }

        return $this->fallbackResolver->resolvePrimaryForVendorSlot($vendorId, $slot);
    }
}
