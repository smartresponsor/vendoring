<?php

declare(strict_types=1);

namespace App\Vendoring\Observability\Service;

use App\Vendoring\ServiceInterface\Observability\MetricCollectorInterface;

/**
 * Composite metric collector that fans one increment call out to multiple collectors.
 */
final readonly class ChainMetricCollector implements MetricCollectorInterface
{
    /**
     * @param iterable<MetricCollectorInterface> $collectors Downstream collectors that will all receive the increment.
     */
    public function __construct(private iterable $collectors) {}

    /**
     * {@inheritdoc}
     */
    public function increment(string $name, array $tags = []): void
    {
        foreach ($this->collectors as $collector) {
            $collector->increment($name, $tags);
        }
    }
}
