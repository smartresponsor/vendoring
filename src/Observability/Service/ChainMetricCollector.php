<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\MetricCollectorInterface;

final class ChainMetricCollector implements MetricCollectorInterface
{
    /**
     * @param iterable<MetricCollectorInterface> $collectors
     */
    public function __construct(private readonly iterable $collectors)
    {
    }

    public function increment(string $name, array $tags = []): void
    {
        foreach ($this->collectors as $collector) {
            $collector->increment($name, $tags);
        }
    }
}
