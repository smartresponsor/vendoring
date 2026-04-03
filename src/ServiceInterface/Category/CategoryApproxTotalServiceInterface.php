<?php

declare(strict_types=1);

namespace App\ServiceInterface\Category;

interface CategoryApproxTotalServiceInterface
{
    /**
     * @return array{value:int,accuracy:string}
     */
    public function get(string $key, bool $withTotal): array;
}
