<?php

declare(strict_types=1);

namespace App\ServiceInterface\Category;

/**
 * Application contract for category approx total service operations.
 */
interface CategoryApproxTotalServiceInterface
{
    /**
     * @return array{value:int,accuracy:string}
     */
    public function get(string $key, bool $withTotal): array;
}
