<?php

declare(strict_types=1);

namespace App\Vendoring\ProviderInterface\Profile;

use App\Vendoring\Projection\VendorPublicProfileSummary;

interface VendorPublicProfileSummaryProviderInterface
{
    public function provideForVendorId(int $vendorId): ?VendorPublicProfileSummary;

    public function provideForCurrentActor(?int $actorId): ?VendorPublicProfileSummary;
}
