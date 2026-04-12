<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Acl;

interface AclRepositoryInterface
{
    /**
     * @param array<string, mixed> $rule
     */
    public function put(array $rule): void;

    /**
     * @param array<string, mixed> $filter
     *
     * @return list<array<string, mixed>>
     */
    public function list(array $filter): array;

    /**
     * @param array<string, mixed> $input
     */
    public function decide(array $input): bool;
}
