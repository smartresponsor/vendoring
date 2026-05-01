<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Observability;

use App\Vendoring\ServiceInterface\Observability\VendorMetricCollectorServiceInterface;
use App\Vendoring\ServiceInterface\Observability\VendorMetricEmitterServiceInterface;

/**
 * In-memory metric collector used for tests and inspection-only runtime scenarios.
 */
final class VendorMetricEmitterService implements VendorMetricCollectorServiceInterface, VendorMetricEmitterServiceInterface
{
    /**
     * @var list<array{name:string,tags:array<string,string>}>
     */
    private array $increments = [];

    /**
     * {@inheritdoc}
     */
    public function increment(string $name, array $tags = []): void
    {
        $this->increments[] = [
            'name' => $name,
            'tags' => $tags,
        ];
    }

    /**
     * Return the inspection snapshot of in-memory metric increments.
     *
     * @return list<array{name:string,tags:array<string,string>}>
     */
    public function snapshot(): array
    {
        return $this->increments;
    }
}
