<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Metric;

use SmartResponsor\Vendor\Port\Metrics\MetricsPort;

final class PromExporter implements MetricsPort
{
    private array $c = [];

    public function inc(string $n): void
    {
        $this->c[$n] = ($this->c[$n] ?? 0) + 1;
    }

    public function gauge(string $n, int|float $v): void
    {
        $this->c[$n] = $v;
    }

    public function dump(): string
    {
        $out = '';
        foreach ($this->c as $k => $v) {
            $out .= $k . ' ' . (string)$v . '\n';
        }
        return $out;
    }
}
