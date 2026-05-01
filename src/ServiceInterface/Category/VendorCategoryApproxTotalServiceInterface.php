<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Category;

interface VendorCategoryApproxTotalServiceInterface
{
    /**
     * @return array{value:int,accuracy:string}
     */
    public function get(string $key, bool $withTotal): array;
}
