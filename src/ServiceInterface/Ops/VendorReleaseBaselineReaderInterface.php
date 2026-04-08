<?php

declare(strict_types=1);

namespace App\ServiceInterface\Ops;

use App\Projection\VendorReleaseBaselineView;

/**
 * Application contract for vendor release baseline reader operations.
 */
interface VendorReleaseBaselineReaderInterface
{
    /**
     * Builds the requested read model.
     */
    public function build(
        string $tenantId,
        string $vendorId,
        ?string $from = null,
        ?string $to = null,
        string $currency = 'USD',
    ): VendorReleaseBaselineView;
}
