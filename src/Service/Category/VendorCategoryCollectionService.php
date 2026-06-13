<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Category;

use App\Vendoring\ServiceInterface\Category\VendorCategoryCollectionServiceInterface;

final class VendorCategoryCollectionService implements VendorCategoryCollectionServiceInterface
{
    /**
     * @param list<array<string, mixed>> $products
     *
     * @return list<array<string, mixed>>
     */
    public function filter(array $products, string $rule): array
    {
        $tokens = preg_split('/\s+/', trim($rule));
        $tokenList = is_array($tokens) ? $tokens : [];
        $operator = 'AND';
        $predicates = [];

        foreach ($tokenList as $token) {
            if ('' === $token) {
                continue;
            }

            if ('AND' === $token || 'OR' === $token) {
                $operator = $token;
                continue;
            }

            if (str_starts_with($token, 'tag:')) {
                $tag = substr($token, 4);
                $predicates[] = static fn(array $product): bool => in_array($tag, self::stringList($product['tags'] ?? null), true);
                continue;
            }

            if (str_starts_with($token, 'category:')) {
                $categoryId = substr($token, 9);
                $predicates[] = static fn(array $product): bool => in_array($categoryId, self::stringList($product['categoryIds'] ?? null), true);
                continue;
            }

            if (1 === preg_match('/^price([<>]=?)(\d+(?:\.\d+)?)$/', $token, $matches)) {
                $comparison = $matches[1];
                $threshold = (float) $matches[2];
                $predicates[] = static function (array $product) use ($comparison, $threshold): bool {
                    $price = is_numeric($product['price'] ?? null) ? (float) $product['price'] : 0.0;

                    return match ($comparison) {
                        '>' => $price > $threshold,
                        '>=' => $price >= $threshold,
                        '<' => $price < $threshold,
                        '<=' => $price <= $threshold,
                        default => false,
                    };
                };
            }
        }

        $result = [];
        foreach ($products as $product) {
            $matched = 'AND' === $operator;
            foreach ($predicates as $predicate) {
                $ok = $predicate($product);
                if ('AND' === $operator) {
                    $matched = $matched && $ok;
                    if (!$matched) {
                        break;
                    }
                    continue;
                }

                $matched = $matched || $ok;
                if ($matched) {
                    break;
                }
            }

            if ($matched) {
                $result[] = $product;
            }
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @return list<string>
     */
    private static function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $result[] = (string) $item;
            }
        }

        return $result;
    }
}
