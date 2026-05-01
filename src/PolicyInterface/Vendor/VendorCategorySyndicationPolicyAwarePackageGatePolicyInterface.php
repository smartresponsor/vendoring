<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface\Vendor;

use App\Vendoring\ValueObject\VendorCategorySyndicationPolicyAwarePackageGateReportValueObject;

interface VendorCategorySyndicationPolicyAwarePackageGatePolicyInterface
{
    /**
     * @param list<string>         $packageMissingRequiredFields
     * @param array<string, mixed> $policyPayload
     * @param array<string, mixed> $fallbackGatePayload
     */
    public function buildReport(
        array $packageMissingRequiredFields,
        array $policyPayload,
        array $fallbackGatePayload,
    ): VendorCategorySyndicationPolicyAwarePackageGateReportValueObject;
}
