<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Service\Category\Rule;

use App\ServiceInterface\Category\CategoryRuleEngineInterface;

/**
 * Application service for category rule engine operations.
 */
final class CategoryRuleEngine implements CategoryRuleEngineInterface
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
        foreach ($this->nodeList($node['all'] ?? null) as $child) {
            if (!$this->evalNode($child, $payload)) {
                return false;
            }
        }
        if (isset($node['all'])) {
            return true;
        }

        foreach ($this->nodeList($node['any'] ?? null) as $child) {
            if ($this->evalNode($child, $payload)) {
                return true;
            }
        }
        if (isset($node['any'])) {
            return false;
        }

        foreach ($this->nodeList($node['none'] ?? null) as $child) {
            if ($this->evalNode($child, $payload)) {
                return false;
            }
        }
        if (isset($node['none'])) {
            return true;
        }

        $attr = isset($node['attr']) && is_scalar($node['attr']) ? (string) $node['attr'] : null;
        $op = isset($node['op']) && is_scalar($node['op']) ? (string) $node['op'] : null;
        $val = $node['value'] ?? null;
        if (null === $attr || null === $op) {
            return false;
        }

        $payloadValue = $payload[$attr] ?? null;

        return match ($op) {
            'eq' => $payloadValue === $val,
            'neq' => $payloadValue !== $val,
            'lt' => is_numeric($payloadValue) && is_numeric($val) && (float) $payloadValue < (float) $val,
            'lte' => is_numeric($payloadValue) && is_numeric($val) && (float) $payloadValue <= (float) $val,
            'gt' => is_numeric($payloadValue) && is_numeric($val) && (float) $payloadValue > (float) $val,
            'gte' => is_numeric($payloadValue) && is_numeric($val) && (float) $payloadValue >= (float) $val,
            'in' => is_array($val) && in_array($payloadValue, $val, true),
            'inTree' => is_scalar($payloadValue) && is_scalar($val) && str_starts_with((string) $payloadValue, (string) $val),
            default => false,
        };
    }

    /**
     * @param mixed $value
     *
     * @return array<string, mixed>
     */
    private function arrayMap(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param mixed $value
     *
     * @return list<array<string, mixed>>
     */
    private function nodeList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
