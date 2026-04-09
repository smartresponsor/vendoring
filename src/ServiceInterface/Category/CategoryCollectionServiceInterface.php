<?php

declare(strict_types=1);

namespace App\ServiceInterface\Category;

interface CategoryCollectionServiceInterface
{
    /**
     * @param list<array<string, mixed>> $products
     *
     * @return list<array<string, mixed>>
     */
    public function filter(array $products, string $rule): array;
}
