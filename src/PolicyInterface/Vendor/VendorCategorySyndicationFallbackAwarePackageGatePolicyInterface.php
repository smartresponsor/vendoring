<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface\Vendor;

use App\Vendoring\DTO\CatalogSyndication\VendorCategorySyndicationFallbackAwarePackageGateReportInputDTO;
use App\Vendoring\ValueObject\VendorCategorySyndicationFallbackAwarePackageGateReportValueObject;

/**
 * @noinspection PhpClassNamingConventionInspection
 */
interface VendorCategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    public function buildReport(VendorCategorySyndicationFallbackAwarePackageGateReportInputDTO $input): VendorCategorySyndicationFallbackAwarePackageGateReportValueObject;
}
