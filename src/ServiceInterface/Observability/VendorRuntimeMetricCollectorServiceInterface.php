<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Observability;

/**
 * In-memory runtime metric collection contract used by the observability backend.
 *
 * Implementations collect deterministic metric envelopes for test inspection while
 * also behaving as standard metric collectors.
 */
interface VendorRuntimeMetricCollectorServiceInterface extends VendorMetricCollectorServiceInterface
{
    /**
     * Return the in-memory inspection snapshot of collected metric records.
     *
     * @return list<array{
     *   'timestamp': string,
     *   'type': string,
     *   'name': string,
     *   'tags': array<string, string>,
     *   'request_id': ?string,
     *   'correlation_id': ?string
     * }>
     */
    public function snapshot(): array;
}
