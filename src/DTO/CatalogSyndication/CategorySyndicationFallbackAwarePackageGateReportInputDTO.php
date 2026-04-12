<?php

declare(strict_types=1);

namespace App\DTO\CatalogSyndication;

final readonly class CategorySyndicationFallbackAwarePackageGateReportInputDTO
{
    /**
     * @param list<string>        $packageMissingRequiredFields
     * @param list<string>        $strictMediaRequiredMissing
     * @param list<string>        $fallbackMediaRequiredMissing
     * @param list<string>        $warnings
     * @param array<string, bool> $strictChecks
     * @param array<string, bool> $fallbackChecks
     * @param list<string>        $exactMatchedBindingIds
     * @param list<string>        $fallbackMatchedBindingIds
     */
    public function __construct(
        public array $packageMissingRequiredFields,
        public array $strictMediaRequiredMissing,
        public array $fallbackMediaRequiredMissing,
        public array $warnings,
        public array $strictChecks,
        public array $fallbackChecks,
        public array $exactMatchedBindingIds,
        public array $fallbackMatchedBindingIds,
    ) {}
}
