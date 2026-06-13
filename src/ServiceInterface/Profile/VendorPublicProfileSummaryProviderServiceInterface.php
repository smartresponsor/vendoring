<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Profile;

use App\Vendoring\Projection\Vendor\VendorPublicProfileSummary;

interface VendorPublicProfileSummaryProviderServiceInterface
{
    public function provideForVendorId(int $vendorId): ?VendorPublicProfileSummary;

    public function provideForCurrentActor(?int $actorId): ?VendorPublicProfileSummary;
}
