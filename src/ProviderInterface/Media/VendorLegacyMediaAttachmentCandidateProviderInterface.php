<?php

declare(strict_types=1);

namespace App\Vendoring\ProviderInterface\Media;

use App\Vendoring\Projection\VendorLegacyMediaAttachmentCandidate;

interface VendorLegacyMediaAttachmentCandidateProviderInterface
{
    /** @return list<VendorLegacyMediaAttachmentCandidate> */
    public function provideForVendorId(int $vendorId): array;
}
