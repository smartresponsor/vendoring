<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface;

use App\Vendoring\DTO\CatalogSyndication\CategorySyndicationFallbackAwarePackageGateReportInputDTO;
use App\Vendoring\ValueObject\CategorySyndicationFallbackAwarePackageGateReport;

/**
 * @noinspection PhpClassNamingConventionInspection
 */
interface CategorySyndicationFallbackAwarePackageGatePolicyInterface
{
    public function buildReport(CategorySyndicationFallbackAwarePackageGateReportInputDTO $input): CategorySyndicationFallbackAwarePackageGateReport;
}
