<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Observability;

/**
 * In-memory metric emitter contract used by tests and inspection-only runtime flows.
 *
 * The emitter behaves like a metric collector, but also exposes an inspection snapshot
 * so smoke tests and unit tests can assert on captured increments.
 */
interface VendorMetricEmitterServiceInterface extends VendorMetricCollectorServiceInterface
{
    /**
     * Return the in-memory inspection snapshot of emitted metric increments.
     *
     * @return list<array{name:string,tags:array<string,string>}>
     */
    public function snapshot(): array;
}
