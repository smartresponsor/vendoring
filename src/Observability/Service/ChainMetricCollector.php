<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\MetricCollectorInterface;

/**
 * Composite metric collector that fans one increment call out to multiple collectors.
 */
final readonly class ChainMetricCollector implements MetricCollectorInterface
{
    /**
     * @param iterable<MetricCollectorInterface> $collectors downstream collectors that will all receive the increment
     */
    public function __construct(private iterable $collectors)
    {
    }

    public function increment(string $name, array $tags = []): void
    {
        foreach ($this->collectors as $collector) {
            $collector->increment($name, $tags);
        }
    }
}
