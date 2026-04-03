<?php

declare(strict_types=1);

namespace App\ServiceInterface\Observability;

/**
 * Write-side contract for forwarding structured observability records to a backend.
 *
 * Implementations may persist NDJSON, ship UDP/TCP telemetry, or delegate to external
 * observability backends, but they must preserve the supplied stream name and payload.
 */
interface ObservabilityRecordExporterInterface
{
    /**
     * Export one structured observability record to the named backend stream.
     *
     * @param string              $stream  Stable stream name such as `runtime_logs` or `runtime_metrics`.
     * @param array<string,mixed> $payload Structured record payload ready for backend export.
     */
    public function export(string $stream, array $payload): void;
}
