<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Rule;

/**
 * Application contract for rule repository operations.
 */
interface RuleRepositoryInterface
{
    /**
     * @param array<string, mixed> $rule
     */
    public function save(array $rule): string;

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array;

    /**
     * @param array<string, mixed> $opt
     *
     * @return list<array<string, mixed>>
     */
    public function list(array $opt = []): array;
}
