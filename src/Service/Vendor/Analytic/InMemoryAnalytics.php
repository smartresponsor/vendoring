<?php
declare(strict_types = 1);

namespace App\Service\Vendor\Analytic;

use SmartResponsor\Vendor\Port\Analytics\AnalyticsPort;

final class InMemoryAnalytics implements AnalyticsPort
{
    private array $c = [];
    private array $h = [];

    public function inc(string $metric, array $labels = []): void
    {
        $key = $metric . '|' . json_encode($labels);
        $this->c[$key] = ($this->c[$key] ?? 0) + 1;
    }

    public function observe(string $metric, float $value, array $labels = []): void
    {
        $key = $metric . '|' . json_encode($labels);
        $this->h[$key] = ($this->h[$key] ?? []);
        $this->h[$key][] = $value;
    }

    public function dump(): array
    {
        return ['counters' => $this->c, 'hist' => $this->h];
    }
}
