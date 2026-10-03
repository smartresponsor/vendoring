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
        [$operator, $predicates] = $this->parseRule($rule);

        return array_values(array_filter(
            $products,
            fn (array $product): bool => $this->matches($product, $operator, $predicates),
        ));
    }

    /**
     * @return array{0: 'AND'|'OR', 1: list<\Closure(array<string, mixed>): bool>}
     */
    private function parseRule(string $rule): array
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

            $predicate = $this->predicateForToken($token);
            if (null !== $predicate) {
                $predicates[] = $predicate;
            }
        }

        return [$operator, $predicates];
    }

    /**
     * @return \Closure(array<string, mixed>): bool|null
     */
    private function predicateForToken(string $token): ?\Closure
    {
        if (str_starts_with($token, 'tag:')) {
            $tag = substr($token, 4);

            return static fn (array $product): bool => in_array($tag, self::stringList($product['tags'] ?? null), true);
        }

        if (str_starts_with($token, 'category:')) {
            $categoryId = substr($token, 9);

            return static fn (array $product): bool => in_array($categoryId, self::stringList($product['categoryIds'] ?? null), true);
        }

        if (1 !== preg_match('/^price([<>]=?)(\d+(?:\.\d+)?)$/', $token, $matches)) {
            return null;
        }

        $comparison = $matches[1];
        $threshold = (float) $matches[2];

        return static function (array $product) use ($comparison, $threshold): bool {
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

    /**
     * @param array<string, mixed>                       $product
     * @param 'AND'|'OR'                                 $operator
     * @param list<\Closure(array<string, mixed>): bool> $predicates
     */
    private function matches(array $product, string $operator, array $predicates): bool
    {
        if ([] === $predicates) {
            return 'AND' === $operator;
        }

        return 'AND' === $operator
            ? !array_any($predicates, static fn (\Closure $predicate): bool => !$predicate($product))
            : array_any($predicates, static fn (\Closure $predicate): bool => $predicate($product));
    }

    /**
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
