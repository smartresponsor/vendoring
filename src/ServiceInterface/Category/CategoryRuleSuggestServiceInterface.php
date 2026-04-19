<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Category;

interface CategoryRuleSuggestServiceInterface
{
    /**
     * @param list<array{price:float, brand?:string, categoryId?:string}> $sample
     *
     * @return array<string, mixed>
     */
    public function suggest(array $sample): array;
}
