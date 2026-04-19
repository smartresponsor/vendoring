<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface;

interface CategorySerializerInterface
{
    /**
     * @param array<string, mixed> $source
     * @param list<string> $includeFieldList
     * @param list<string> $excludeFieldList
     *
     * @return array<string, mixed>
     */
    public function serialize(array $source, array $includeFieldList, array $excludeFieldList): array;
}
