<?php

declare(strict_types=1);

namespace App\Vendoring\PolicyInterface\Vendor;

interface VendorCategorySyndicationMappingPolicyInterface
{
    public function assertLocaleMode(string $localeMode): void;

    /**
     * @param array<string, string> $fieldMap
     *
     * @return array<string, string>
     */
    public function normalizeFieldMap(array $fieldMap): array;

    /**
     * @param list<string> $requiredFields
     *
     * @return list<string>
     */
    public function normalizeRequiredFields(array $requiredFields): array;
}
