<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Catalog;

use App\Vendoring\ServiceInterface\Catalog\VendorCategorySerializerServiceInterface;

final class VendorCategorySerializerService implements VendorCategorySerializerServiceInterface
{
    /**
     * @param array<string, mixed> $source
     * @param list<string> $includeFieldList
     * @param list<string> $excludeFieldList
     *
     * @return array<string, mixed>
     */
    public function serialize(array $source, array $includeFieldList, array $excludeFieldList): array
    {
        $result = $source;

        if ([] !== $includeFieldList) {
            $result = array_intersect_key($result, array_flip($includeFieldList));
        }

        foreach ($excludeFieldList as $key) {
            unset($result[$key]);
        }

        return $result;
    }
}
