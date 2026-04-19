<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Category\Suggest;

use App\Vendoring\ServiceInterface\Category\CategoryRuleSuggestServiceInterface;

final class CategoryRuleSuggestService implements CategoryRuleSuggestServiceInterface
{
    /**
     * Build a simple heuristic rule: pick most frequent brand and 80th percentile price threshold.
     *
     * @param list<array{price:float, brand?:string, categoryId?:string}> $sample
     *
     * @return array<string,mixed>
     */
    public function suggest(array $sample): array
    {
        if ([] === $sample) {
            return ['any' => [['attr' => 'price', 'op' => 'lte', 'value' => 100]]];
        }

        $brands = [];
        $prices = [];
        foreach ($sample as $product) {
            if (isset($product['brand'])) {
                $brands[$product['brand']] = ($brands[$product['brand']] ?? 0) + 1;
            }

            $prices[] = $product['price'];
        }

        arsort($brands);
        sort($prices);
        $index = (int) floor(0.8 * max(0, count($prices) - 1));
        $p80 = $prices[$index] ?? 100.0;
        $topBrand = array_key_first($brands);

        if (is_string($topBrand) && '' !== $topBrand) {
            return ['all' => [
                ['attr' => 'brand', 'op' => 'eq', 'value' => $topBrand],
                ['attr' => 'price', 'op' => 'lte', 'value' => $p80],
            ]];
        }

        return ['any' => [['attr' => 'price', 'op' => 'lte', 'value' => $p80]]];
    }
}
