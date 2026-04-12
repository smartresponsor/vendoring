<?php

declare(strict_types=1);

namespace App\PolicyInterface;

use App\DTO\CatalogSyndication\CategorySyndicationFallbackAwarePackageGateReportInputDTO;
use App\ValueObject\CategorySyndicationFallbackAwarePackageGateReport;

/**
 * @noinspection PhpClassNamingConventionInspection
 */
interface CategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    public function buildReport(CategorySyndicationFallbackAwarePackageGateReportInputDTO $input): CategorySyndicationFallbackAwarePackageGateReport;
}
