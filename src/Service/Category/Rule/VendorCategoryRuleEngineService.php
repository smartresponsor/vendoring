<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Category\Rule;

use App\Vendoring\ServiceInterface\Category\Rule\VendorCategoryRuleEngineServiceInterface;

final class VendorCategoryRuleEngineService implements VendorCategoryRuleEngineServiceInterface
{
    /**
     * @param array<string, mixed> $rule
     * @param array<string, mixed> $payload
     */
    public function match(array $rule, array $payload): bool
    {
        return $this->evalNode($this->arrayMap($rule['condition'] ?? null), $payload);
    }

    /**
     * @param array<string, mixed> $node
     * @param array<string, mixed> $payload
     */
    private function evalNode(array $node, array $payload): bool
    {
        foreach (['all', 'any', 'none'] as $operator) {
            if (array_key_exists($operator, $node)) {
                return $this->evalGroup($operator, $node[$operator], $payload);
            }
        }

        return $this->evalComparison($node, $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function evalGroup(string $operator, mixed $value, array $payload): bool
    {
        $children = $this->nodeList($value);

        return match ($operator) {
            'all' => !array_any($children, fn ($child) => !$this->evalNode($child, $payload)),
            'any' => array_any($children, fn ($child) => $this->evalNode($child, $payload)),
            'none' => !array_any($children, fn ($child) => $this->evalNode($child, $payload)),
            default => false,
        };
    }

    /**
     * @param array<string, mixed> $node
     * @param array<string, mixed> $payload
     */
    private function evalComparison(array $node, array $payload): bool
    {
        if (!isset($node['attr'], $node['op']) || !is_scalar($node['attr']) || !is_scalar($node['op'])) {
            return false;
        }

        $payloadValue = $payload[(string) $node['attr']] ?? null;
        $value = $node['value'] ?? null;

        return match ((string) $node['op']) {
            'eq' => $payloadValue === $value,
            'neq' => $payloadValue !== $value,
            'lt' => is_numeric($payloadValue) && is_numeric($value) && (float) $payloadValue < (float) $value,
            'lte' => is_numeric($payloadValue) && is_numeric($value) && (float) $payloadValue <= (float) $value,
            'gt' => is_numeric($payloadValue) && is_numeric($value) && (float) $payloadValue > (float) $value,
            'gte' => is_numeric($payloadValue) && is_numeric($value) && (float) $payloadValue >= (float) $value,
            'in' => is_array($value) && in_array($payloadValue, $value, true),
            'inTree' => is_scalar($payloadValue) && is_scalar($value) && str_starts_with((string) $payloadValue, (string) $value),
            default => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function arrayMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_filter($value, static function ($key): bool {
            return is_string($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function nodeList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }

            $normalized = array_filter($item, function ($key) {
                return is_string($key);
            }, ARRAY_FILTER_USE_KEY);

            $result[] = $normalized;
        }

        return $result;
    }
}
