<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Category;

interface CategoryRuleEngineInterface
{
    /**
     * @param array<string, mixed> $rule
     * @param array<string, mixed> $payload
     */
    public function match(array $rule, array $payload): bool;
}
