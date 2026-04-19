<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Observability;

/**
 * Write-side contract for structured runtime logging.
 *
 * Implementations are expected to emit an operationally stable log envelope for
 * application flows while preserving the supplied message and contextual fields.
 */
interface RuntimeLoggerInterface
{
    /**
     * Record an informational runtime event.
     *
     * @param string                    $message Human-readable event message.
     * @param array<string, scalar|null> $context Structured operational context such as route,
     *                                            vendor_id, transaction_id, and error_code.
     */
    public function info(string $message, array $context = []): void;

    /**
     * Record a warning-level runtime event.
     *
     * @param string                    $message Human-readable warning message.
     * @param array<string, scalar|null> $context Structured operational context such as route,
     *                                            vendor_id, transaction_id, and error_code.
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Record an error-level runtime event.
     *
     * @param string                    $message Human-readable error message.
     * @param array<string, scalar|null> $context Structured operational context such as route,
     *                                            vendor_id, transaction_id, and error_code.
     */
    public function error(string $message, array $context = []): void;

    /**
     * Return the in-memory inspection snapshot of emitted log records.
     *
     * @return list<array<string, scalar|null>> Stable structured records captured by the logger.
     */
    public function snapshot(): array;
}
