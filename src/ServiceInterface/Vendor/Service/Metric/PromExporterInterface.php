<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Metric;

interface PromExporterInterface
{

    public function inc(string $n): void;

    public function gauge(string $n, int|float $v): void;

    public function dump(): string;
}
