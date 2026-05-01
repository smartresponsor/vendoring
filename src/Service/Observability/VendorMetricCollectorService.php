<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Observability;

use App\Vendoring\ServiceInterface\Observability\VendorMetricCollectorServiceInterface;

/**
 * Composite metric collector that fans one increment call out to multiple collectors.
 */
final readonly class VendorMetricCollectorService implements VendorMetricCollectorServiceInterface
{
    /**
     * @param iterable<VendorMetricCollectorServiceInterface> $collectors Downstream collectors that will all receive the increment.
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
