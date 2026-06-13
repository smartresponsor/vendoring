<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Media;

use App\Vendoring\Projection\Vendor\VendorLegacyMediaAttachmentCandidate;

interface VendorLegacyMediaAttachmentCandidateProviderServiceInterface
{
    /** @return list<VendorLegacyMediaAttachmentCandidate> */
    public function provideForVendorId(int $vendorId): array;
}
