<?php

declare(strict_types=1);

namespace App\Vendoring\Policy;

use App\Vendoring\PolicyInterface\CategorySyndicationMappingPolicyInterface;
use InvalidArgumentException;

final class CategorySyndicationMappingPolicy implements CategorySyndicationMappingPolicyInterface
{
    private const array LOCALE_MODES = ['per_locale', 'shared'];

    public function assertLocaleMode(string $localeMode): void
    {
        $normalized = trim($localeMode);
        if (!in_array($normalized, self::LOCALE_MODES, true)) {
            throw new InvalidArgumentException(sprintf('unsupported_locale_mode:%s', $localeMode));
        }
    }

    public function normalizeFieldMap(array $fieldMap): array
    {
        $result = [];
        foreach ($fieldMap as $sourceField => $targetField) {
            $source = trim((string) $sourceField);
            $target = trim($targetField);
            if ('' !== $source && '' !== $target) {
                $result[$source] = $target;
            }
        }

        return $result;
    }

    public function normalizeRequiredFields(array $requiredFields): array
    {
        $result = [];
        foreach ($requiredFields as $requiredField) {
            $normalized = trim($requiredField);
            if ('' !== $normalized && !in_array($normalized, $result, true)) {
                $result[] = $normalized;
            }
        }

        return $result;
    }
}
