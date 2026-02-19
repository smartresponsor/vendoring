<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Analytic;

interface InMemoryAnalyticsInterface
{

    public function inc(string $metric, array $labels = []): void;

    public function observe(string $metric, float $value, array $labels = []): void;

    public function dump(): array;
}
